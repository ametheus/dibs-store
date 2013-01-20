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
		
		// Voucher type
		$type = preg_replace( "/[^a-zA-Z_]+/", "_", $voucher["type"] );
		
		if ( !empty($voucher["cart_id"]) && ($oc = Cart::get($voucher["cart_id"])) )
		{
			if ( count($oc["status"]["confirmed"]) > 0 
				&& count($oc["status"]["cancelled"]) == 0 
				&& count($oc["status"]["expired"]) == 0 )
				return false;
			
			// TODO: Fancy auto-loading plugin system.
			if ( method_exists( __CLASS__, "_v_deinstall_{$type}" ) )
				call_user_func( array(__CLASS__, "_v_deinstall_{$type}"), $cart_id, $voucher );
		}
		
		db()->vouchers->update(
			array( "_id" => $voucher_code ),
			array( '$set' => array( "cart_id" => $cart_id ) )
		);
		
		// TODO: Fancy auto-loading plugin system.
		if ( method_exists( __CLASS__, "_v_install_{$type}" ) )
			call_user_func( array(__CLASS__, "_v_install_{$type}"), $cart_id, $voucher );
		
		return Cart::get( $cart_id );
	}
	
	
	
	/**
	 * Install / deinstall code for "cart-item" plugin types.
	 **/
	static function _v_install_cart_item( $cart_id, $voucher )
	{
		$vd = $voucher["data"];
		if ( !isset($vd["EAN"]) )         $vd["EAN"] = "VOUCHER";
		if ( !isset($vd["title"]) )       $vd["title"] = "Redeemed voucher";
		if ( !isset($vd["description"]) ) $vd["description"] = "";
		if ( !isset($vd["count"]) )       $vd["count"] = 1;
		
		$vd["voucher-code"] = $voucher["_id"];
		
		$rv = db()->carts->update(
			array( "cart-id" => $cart_id ),
			array( '$push' => array("items" => $vd ) )
		);
		
		return $rv > 0;
	}
	static function _v_deinstall_cart_item( $cart_id, $voucher )
	{
		$cart = Cart::get($cart_id);
		
		$rv = false;
		foreach ( $cart["items"] as $i => $it )
		{
			if ( $it["voucher-code"] != $voucher["_id"] ) continue;
			
			// FIXME: is it possible to remove an array element in one query?
			db()->carts->update(
				array( "cart-id" => $cart_id ),
				array( '$unset' => array( "items.{$i}" => 1 ) )
			);
			db()->carts->update(
				array( "cart-id" => $cart_id ),
				array( '$pull' => array( "items" => null ) )
			);
			$rv = true;
			break;
		}
		
		return $rv;
	}
}







