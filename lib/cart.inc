<?php 

/*
	Copyright (C) 2012 Thijs van Dijk
	
	This file is part of dibs.

	dibs is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	dibs is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with dibs.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
	Methods for viewing or updating a shopping cart.
*/


class Cart
{
	/**
	 * Add or replace an address {address} with optional {label}
	 **/
	static function add_address( $cart_id, $address, $label = "default" )
	{
		$label = str_replace( array(".","\$"), "-", $label );
		
		$rv = db()->carts->update(
			array( "cart-id" => $cart_id ),
			array( '$set' => array( "address.{$label}" => $address ) )
		);
		
		return $rv > 0;
	}
	
	
	/**
	 * Add {count} items of type {EAN} to the cart.
	 **/
	static function add_item( $cart_id, $EAN, $count )
	{
		$db = db();
		
		// If this particular item was already in the cart, add the two totals.
		$rv = $db->carts->update(
			array(
				"cart-id" => $cart_id,
				"items.EAN" => $EAN
			),
			array( '$inc' => array('items.$.count' => $count ) )
		);
		
		if ( $rv == 0 ) return false;
		
		// Check if 'affected rows' > 0
		$le = $db->lastError();
		if ( $le["n"] > 0 ) return true;
		
		$rv = $db->carts->update(
			array( "cart-id" => $cart_id ),
			array( '$push' => array("items" => array(
				"EAN" => $EAN,
				"count" => $count ) ) )
		);
		
		return $rv > 0;
	}
	
	
	/**
	 * Add {status} to a cart
	 **/
	static function add_status( $cart_id, $status, $user = "system" )
	{
		$status = str_replace( array(".","\$"), "-", $status );
		
		$rv = db()->carts->update(
			array( "cart-id" => $cart_id ),
			array( '$push' =>
				array( "status.{$status}" => array(
					"date" => new MongoDate(),
					"user" => (string)$user
				) )
			)
		);
		
		return $rv > 0;
	}
	
	
	/** 
	 * Create a new cart in the database, and return its ID.
	 * 
	 * A cart ID is - in this implementation - a string satisfying the regex
	 *    /([0-9a-zA-Z]{5}-){2,8}[0-9a-zA-Z]{1,5}/
	 * and has nice cryptographic properties
	 * 
	 * @param int tries  The number of times to generate an ID until giving up.
	 **/
	static function create( $tries = 10 )
	{
		$db = db();
		
		if ( $tries < 1 ) return null;
		
		$rv = "";
		for ( $i = 0; $i < 32; $i++ )
			$rv .= chr(mt_rand(0,255));
		
		$rv = base64_encode( hash( "sha256", $rv, $raw = true ) );
		// HACK: we actually need base62_encode.
		$rv = str_replace( array("/","+","="), "", $rv );
		
		for( $i = 5; $i < strlen($rv); $i+= 6 )
			$rv = substr($rv,0,$i) . "-" . substr($rv,$i);
		
		if ( strlen($rv) < 12 || Cart::exists( $rv ) )
		{
			// The possibility that we just found a SHA-256 collision is
			// slightly unnerving, but irrelevant to the user.
			// Just try again.
			return self::create( $tries - 1 );
		}
		
		// Great, we've managed to generate an ID.
		// Now, create this cart in the database.
		$db->carts->insert(array(
			"cart-id" => $rv,
			"created" => time(),
			"ip-addr" => $_SERVER["REMOTE_ADDR"],
		));
		
		return $rv;
	}


	/** 
	 * Check if the cart exists in the database
	 **/
	static function exists( $cart_id )
	{
		$db = db();
		
		return $db->carts->find( array(
			"cart-id" => $cart_id
		) )->count() > 0;
	}


	/**
	 * Return the cart object for the cart specified
	 **/
	static function get( $cart_id )
	{
		$db = db();
		return $db->carts->findOne( array(
			"cart-id" => $cart_id
		) );
	}
	
	
	/**
	 * Check if {cart} has status {status}
	 **/
	static function has_status( $cart_id, $status )
	{
		$status = str_replace( array(".","\$"), "-", $status );
		
		$cart = db()->carts->findOne(
			array( "cart-id" => $cart_id ),
			array( "status.{$status}" => 1 )
		);
		if ( !$cart ) return false;
		
		$statuses = @$cart["status"][$status];
		$c = count( $statuses );
		
		if ( $c == 0 ) return false;
		return $statuses[ $c - 1 ];
	}
	
	
	/**
	 * Check if the cart is still open
	 **/
	static function is_open( $cart_id )
	{
		return ! self::has_status( $cart_id, "confirmed" );
	}
	
	
	/** 
	 * Check if specified cart has an {i}th order line
	 **/
	static function line_exists( $cart_id, $i )
	{
		if ( $i < 0 ) return false;
		
		// HACK: Find a way to do this directly in MongoDB.
		$cart = db()->carts->findOne(
			array( "cart-id" => $cart_id ),
			array( "items" => 1 )
		);
		return $i < count( $cart["items"] );
	}
	
	
	/**
	 * Check if the specified order line is special or not
	 **/
	static function line_is_special( $cart_id, $i )
	{
		if ( $i < 0 ) return false;
		
		// In this implementation, an order line is 'special' if its EAN is
		// non-numeric.
		
		$cart = db()->carts->findOne(
			array( "cart-id" => $cart_id ),
			array( "items.EAN" => 1 )
		);
		$EAN = $cart["items"][$i]["EAN"];
		
		return !preg_match('/^[-0-9]+$/',$EAN);
	}
	
	
	/**
	 * Update the order line to read exactly {count} items.
	 **/
	static function set_count( $cart_id, $i, $count )
	{
		if ( $i < 0 ) return false;
		
		db()->carts->update(
			array( "cart-id" => $cart_id ),
			array( '$set' => array( "items.{$i}.count" => $count ) )
		);
		
		// Check if 'affected rows' > 0
		$le = db()->lastError();
		return ( $le["n"] > 0 );
	}
	
	
	/**
	 * Get the total amount on this cart
	 **/
	static function total_amount( $cart_id, $currency="EUR" )
	{
		// TODO: convert currencies
		
		$cart = db()->carts->findOne(
			array( "cart-id" => $cart_id ),
			array( "items" => 1 )
		);
		
		$due = 0;
		foreach ( $cart["items"] as $item )
		{
			$count = isset($item["count"]) ? (int)$item["count"] : 1;
			if ( isset($item["grand-total"]) )
			{
				$due += $item["grand-total"]["amount"];
			}
			elseif ( isset($item["price"]) )
			{
				$due += $count * $item["price"]["amount"];
			}
			elseif ( isset($item["EAN"]) )
			{
				$it = Item::get( (string)$item["EAN"] );
				$due += $count * $it["price"]["amount"];
			}
		}
		
		return $due;
	}
}
