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
	API Manager for v1 calls.
*/

function handle_request( $uri, &$output )
{
	$verb = $_SERVER["REQUEST_METHOD"];
	
	/**
	 * Targets GET .../1/cart/... or GET .../1/confirm/...
	 * 
	 * Provide methods for viewing or updating shopping carts.
	 **/
	if ( substr($uri,0,5) == "cart/" || substr($uri,0,8) == "confirm/" )
	{
		require_once( "v1/cart.php" );
		return handle_cart_request( $uri, $output );
	}
	
	/**
	 * Targets GET .../1/product/... or GET .../1/category/...
	 * 
	 * Provide methods for obtaining product information
	 **/
	if ( substr($uri,0,8) == "product/" || substr($uri,0,9) == "category/" )
	{
		require_once( "v1/product.php" );
		return handle_item_request( $uri, $output );
	}
	
	/**
	 * Targets GET .../1/ideal/...
	 * 
	 * Process Payments using iDEAL
	 **/
	if ( substr($uri,0,6) == "ideal/" )
	{
		require_once( "v1/ideal.php" );
		return handle_ideal_request( $uri, $output );
	}
	
	print( "Most handlers are currently in development, " .
		"others aren't yet conceived, written, or performed." );
	return 2;
}

