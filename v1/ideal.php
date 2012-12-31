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
	Handle iDEAL payments
*/


require_once( "lib/cart.inc" );
require_once( "lib/item.inc" );
require_once( "lib/invoice.inc" );
require_once( "lib/payment/sisow-ideal.inc" );


function handle_ideal_request( $uri, &$output )
{
	$verb = $_SERVER["REQUEST_METHOD"];
	
	
	/**
	 * GET .../1/ideal/pay/{cart-id}
	 *
	 * Initiate an iDEAL payment to pay for the contents of this cart
	 **/
	if ( preg_match( '#^ideal/pay/([-0-9a-zA-z]+)/?$#', $uri, $A ) )
	{
		$cart_id = $A[1];
		if ( !Cart::exists( $cart_id ) ) return 3;
		if ( Cart::is_open( $cart_id ) ) return 10;
		
		$td = Cart::total_amount( $cart_id );
		if ( $td < 0.005 ) return 11;
		
		$inv = Invoice::assign( $cart_id );
		if ( ! $inv ) return 1;
		
		$url = SisowIdeal::initiate_payment( $cart_id, null, $inv );
		
		$output = array( "redirect-url" => $url );
		return 0;
	}
	
	
	print( "Invalid iDEAL operation" );
	return 2;
}

