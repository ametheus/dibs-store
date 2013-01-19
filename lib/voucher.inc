<?php 

/*
	Copyright (C) 2013 Thijs van Dijk
	
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
	Handling vouchers
*/


class Voucher
{
	
	/**
	 * Assign a voucher to a cart
	 **/
	static function assign( $voucher_code, $cart_id )
	{
		if ( !Cart::exists($cart_id) ) return null;
		
		$voucher = db()->vouchers->findOne( array("_id" => $voucher_code ) );
		if ( !$voucher ) return null;
		
		if ( !empty($voucher["cart_id"]) && ($oc = Cart::get($voucher["cart_id"])) )
		{
			if ( count($oc["status"]["confirmed"]) > 0 
				&& count($oc["status"]["cancelled"]) == 0 
				&& count($oc["status"]["expired"]) == 0 )
				return false;
			
			// TODO: 'deinstall' voucher
		}
		
		db()->vouchers->update(
			array( "_id" => $voucher_code ),
			array( '$set' => array( "cart_id" => $cart_id ) )
		);
		
		// TODO: 'install' voucher
		
		return Cart::get( $cart_id );
	}
	
	
}







