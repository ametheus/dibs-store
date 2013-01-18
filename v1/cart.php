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
	Handles .../1/cart/... calls.
*/

require_once( "lib/cart.inc" );
require_once( "lib/item.inc" );
require_once( "lib/invoice.inc" );



function handle_cart_request( $uri, &$output )
{
	$verb = $_SERVER["REQUEST_METHOD"];
	
	
	/**
	 * GET .../1/cart
	 *
	 * Create a new, empty cart.
	 **/
	if ( $verb == "GET" && strlen($uri) <= 5 )
	{
		$cart_id = Cart::create();
		
		// Flat-rate shipping costs
		$rv = Cart::add_item( $cart_id, "PORTO", 1 );
		// Add the status 'created'
		Cart::add_status( $cart_id, "created" );
		
		$output = array( "cart-id" => $cart_id );
		return Cart::exists( $cart_id ) ? 0 : 1;
	}
	
	
	
	/** 
	 * GET .../1/cart/{cart-id}
	 * 
	 * Return the cart's current contents.
	 **/
	if ( $verb == "GET"
		&& preg_match( '#^cart/([-0-9a-zA-z]+)/?$#', $uri, $A ) )
	{
		$cart_id = $A[1];
		if ( !Cart::exists( $cart_id ) ) return 3;
		
		$output = Cart::get( $cart_id );
		return 0;
	}
	
	
	
	/** 
	 * POST .../1/cart/{cart-id}   {EAN} {count}
	 * 
	 * Add {count} items of {EAN} to the cart.
	 **/
	if ( $verb == "POST"
		&& preg_match( '#^cart/([-0-9a-zA-z]+)/?$#', $uri, $A )
		&& isset($_POST["EAN"]) && isset($_POST["count"]) )
	{
		$cart_id = $A[1];
		$EAN     = (string)$_POST["EAN"];
		$count   = (int)$_POST["count"];
		
		if ( !Cart::exists( $cart_id ) ) return 3;
		if ( !Item::exists( $EAN ) ) return 4;
		if ( !Cart::is_open( $cart_id ) ) return 5;
		if ( $count <= 0 ) return 6;
		
		$rv = Cart::add_item( $cart_id, $EAN, $count );
		if ( $rv )
		{
			$output = Cart::get( $cart_id );
			return 0;
		}
		return 1;
	}
	
	
	
	/** 
	 * POST .../1/cart/{cart-id}/{orderline}   {count}
	 * 
	 * Update {orderline} to have {count} items. (Orderline is zero-indexed!)
	 **/
	if ( $verb == "POST"
		&& preg_match( '#^cart/([-0-9a-zA-z]+)/(\d+)/?$#', $uri, $A )
		&& isset($_POST["count"]) )
	{
		$cart_id = $A[1];
		$line_no = (int)$A[2];
		$count   = (int)$_POST["count"];
		
		if ( !Cart::exists( $cart_id ) ) return 3;
		if ( !Cart::is_open( $cart_id ) ) return 5;
		if ( $count < 0 ) return 6;
		if ( !Cart::line_exists( $cart_id, $line_no ) ) return 7;
		if ( Cart::line_is_special( $cart_id, $line_no ) ) return 8;
		
		$rv = Cart::set_count( $cart_id, $line_no, $count );
		if ( $rv )
		{
			$output = Cart::get( $cart_id );
			return 0;
		}
		return 1;
	}
	
	
	/** 
	 * POST .../1/confirm/{cart-id}   {e-mail} {delivery addr.}  [{billing addr.}] 
	 * 
	 * Confirm the order, and add given addresses to it.
	 **/
	if ( $verb == "POST"
		&& preg_match( '#^confirm/([-0-9a-zA-z]+)/?$#', $uri, $A )
		&& !empty($_POST["email"])
		&& !empty($_POST["del-street1"]) && !empty($_POST["del-postcode"]) )
	{
		$rv = true;
		$cart_id = $A[1];
		
		// Cap the e-mail address at 255 chars. (CVE-2010-3710)
		$email = substr( $_POST["email"], 0, 255 );
		
		if ( !Cart::exists( $cart_id ) ) return 3;
		if ( !Cart::is_open( $cart_id ) ) return 5;
		if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) return 9;
		
		// Add e-mail address
		$rv = $rv && Cart::set( $cart_id, array( "email" => $email ) );
		
		// Add addresses
		$address = get_address( "del-" );
		$rv = $rv && Cart::add_address( $cart_id, $address, "delivery" );
		
		if ( isset($_POST["bill-street1"]) && isset($_POST["bill-postcode"]) )
			$rv = $rv && Cart::add_address( $cart_id, get_address( "bill-" ), "billing" );
		else
			$rv = $rv && Cart::add_address( $cart_id, $address, "billing" );
		
		// Fix the price info
		Cart::freeze_prices( $cart_id );
		
		// Set the status "confirmed"
		$rv = $rv && Cart::add_status( $cart_id, "confirmed" );
		
		// Assign an invoice number
		Invoice::assign( $cart_id );
		
		if ( $rv )
		{
			$output = Cart::get( $cart_id );
			return 0;
		}
		return 1;
	}
	
	
	print( "This is not a cart operation I can relate to." );
	return 2;
}


function get_address( $prefix = "" )
{
	$rv = array();
	$p = $prefix;
	
	if ( !empty($_POST["{$p}name"]) )       $rv["name"]       = (string)$_POST["{$p}name"];
	if ( !empty($_POST["{$p}company"]) )    $rv["company"]    = (string)$_POST["{$p}company"];
	if ( !empty($_POST["{$p}department"]) ) $rv["department"] = (string)$_POST["{$p}department"];
	if ( !empty($_POST["{$p}street1"]) )    $rv["street1"]    = (string)$_POST["{$p}street1"];
	if ( !empty($_POST["{$p}street2"]) )    $rv["street2"]    = (string)$_POST["{$p}street2"];
	if ( !empty($_POST["{$p}postcode"]) )   $rv["postcode"]   = (string)$_POST["{$p}postcode"];
	if ( !empty($_POST["{$p}city"]) )       $rv["city"]       = (string)$_POST["{$p}city"];
	if ( !empty($_POST["{$p}state"]) )      $rv["state"]      = (string)$_POST["{$p}state"];
	if ( !empty($_POST["{$p}country"]) )    $rv["country"]    = (string)$_POST["{$p}country"];
	if ( !empty($_POST["{$p}planet"]) )     $rv["planet"]     = (string)$_POST["{$p}planet"];
	
	if ( !empty($_POST["{$p}phone"]) )      $rv["phone"]      = (string)$_POST["{$p}phone"];
	
	return $rv;
}

