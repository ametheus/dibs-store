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
	 * Create a new cart in the database, and return its ID
	 **/
	static function create()
	{
		return null;
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
