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
	Handle Sisow iDeal payments.
*/


class SisowIdeal
{
	static function initiate_payment( $cart_id, $bank_id, $invoice_no )
	{
		global $sisow_merchant_id, $sisow_merchant_key;
		global $store_name;
		global $api_use_https, $api_host, $api_root;
		
		$shop_id = "dibs"; // To be implemented by Sisow
		$amount = round( 100 * Cart::total_amount( $cart_id, "EUR" ) );
		$unique_code = "TODO"; // TODO
		
		$api_base = ( $api_use_https ? "https" : "http" ) . "://" . $api_host . $api_root;
		
		$url = "https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/TransactionRequest";
		$query = array(
			"shopid"        => $shop_id,
			"merchantid"    => $sisow_merchant_id,
			"payment"       => "",
			"purchaseid"    => $invoice_no,
			"amount"        => $amount,
			"entrancecode"  => $unique_code,
			"description"   => $store_name,
			"returnurl"     => $api_base . "1/ideal/ok/{$cart_id}",
			"cancelurl"     => $api_base . "1/ideal/cancel/{$cart_id}",
			"callbackurl"   => $api_base . "1/ideal/cb/{$cart_id}",
		);
		
		if ( $bank_id )
			$query["issuerid"] = $bank_id;
		
		$pt = $invoice_no . $unique_code . $amount . $shop_id .
			$sisow_merchant_id . $sisow_merchant_key;
		$query["sha1"] = sha1($pt);
		
		$response = file_get_contents( $url . "?" . http_build_query($query) );
		$response = @simplexml_load_string($response);
		if ( !$response ) return false;
		
		if ( $response->getName() == "errorresponse" )
		{
			$errno = (string)$response->error->errorcode;
			$error = (string)$response->error->errormessage;
			error_log( "init fail: Cart [{$cart_id}], error code [{$errno}] message [{$error}]\n", 3, "/tmp/sisow-ideal.log" );
			return false;
		}
		
		$trxid = (string)$response->transaction->trxid;
		error_log( "init debug: Cart [{$cart_id}], xml " . print_r($response,true) . " \n", 3, "/tmp/sisow-ideal.log" );
		db()->carts->update(
			array( "cart-id" => $cart_id ),
			array( 
				'$set' => array( "sisow-trxid" => $trxid ),
				'$push' => array( "sisow-sessions" => $trxid ),
			)
		);
		
		return urldecode( $response->transaction->issuerurl );
	}
	
	
	static function verify_payment( $cart_id, $unique_code )
	{
		// TODO: unieke code.
		if ( $unique_code !== "TODO" ) return false;
		
		global $sisow_merchant_id, $sisow_merchant_key;
		$shop_id = "dibs";
		
		$cart = db()->carts->findOne( 
			array( "cart-id" => $cart_id ),
			array( "sisow-trxid" => 1 )
		);
		if ( !$cart ) return false;
		$trxid = $cart["sisow-trxid"];
		if ( !$trxid ) return false;
		
		$url = "https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/StatusRequest";
		$query = array(
			"trxid" => $trxid,
			"shopid" => $shop_id,
			"merchantid" => $sisow_merchant_id,
		);
		
		$pt = $trxid . $shop_id . $sisow_merchant_id . $sisow_merchant_key;
		$query["sha1"] = sha1($pt);
		
		$response = file_get_contents( $url . "?" . http_build_query($query) );
		$response = @simplexml_load_string($response);
		if ( !$response ) return false;
		
		if ( $response->getName() == "errorresponse" )
		{
			$errno = (string)$response->error->errorcode;
			$error = (string)$response->error->errormessage;
			error_log( "verify fail: Cart [{$cart_id}], error code [{$errno}] message [{$error}]\n", 3, "/tmp/sisow-ideal.log" );
			return false;
		}
		
		return array(
			"status" => strtolower( $response->transaction->status ),
			"issuer" => (string)$response->transaction->issuerid,
			"amount" => (float)$response->transaction->amount / 100,
		);
	}
}

