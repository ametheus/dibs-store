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
	Methods for viewing / updating a shopping cart.
*/


class Cart
{
	/** 
	 * Create a new cart in the database, and return its ID.
	 * 
	 * A cart ID is a string satisfying the regex
	 *    /([0-9a-zA-Z]{5}-){2,8}[0-9a-zA-Z]{1,5}/
	 * and has nice cryptographic properties
	 * 
	 * @param int tries  The number of times to generate an ID until giving up.
	 **/
	static function create( $tries = 10 )
	{
		global $db;
		
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
		return false;
	}


	/**
	 * Return the cart object for the cart specified
	 **/
	static function get( $cart_id )
	{
		return null;
	}
	
	
	/**
	 * Check if the cart is still open
	 **/
	static function is_open( $cart_id )
	{
		return false;
	}
	
	
	static function add_item( $cart_id, $EAN, $count )
	{
		
	}
}
