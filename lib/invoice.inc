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
	Add invoices to orders
*/


class Invoice
{
	/**
	 * Assign an invoice number to an order.
	 **/
	static function assign( $cart_id )
	{
		$cart = db()->carts->findOne( array("cart-id" => $cart_id), array("invoice-no" => 1) );
		if ( !$cart ) return null;
		
		if ( (int)$cart["invoice-no"] > 0 ) return (int)$cart["invoice-no"];
		
		$inv = inc_counter("invoice-no");
		Cart::set( $cart_id, array( "invoice-no" => $inv ) );
		Cart::add_status( $cart_id, "invoice assigned" );
		
		return $inv;
	}
}
