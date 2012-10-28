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
		
		$rv = Cart::add_item( $cart_id, $EAN, $count );
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

