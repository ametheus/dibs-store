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
	protected static function request( $type, $query = array() )
	{
		$url = "https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/{$type}";
		if ( count($query) )
			$url .= "?" . http_build_query($query);
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_VERBOSE, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt( $ch, CURLOPT_CAINFO, dirname(__FILE__) . "/sisow.nl.pem" );
		
		// Get the response and close the channel.
		$txt = curl_exec($ch);
		curl_close($ch);
		
		$response = @simplexml_load_string($txt);
		
		$d = date( "[Y-m-d H:i:s] " );
		if ( !$response )
		{
			error_log( "{$d}{$type} fail: Invalid XML [{$txt}]\n\n", 3, "/tmp/sisow-ideal.log" );
			return false;
		}
		
		if ( $response->getName() == "errorresponse" )
		{
			$errno = (string)$response->error->errorcode;
			$error = (string)$response->error->errormessage;
			error_log( "{$d}{$type} fail: Cart [{$cart_id}], error code [{$errno}] message [{$error}]\n\n", 3, "/tmp/sisow-ideal.log" );
			return false;
		}
		
		return $response;
	}
	
	public static function initiate_payment( $cart_id, $bank_id, $invoice_no )
	{
		global $sisow_merchant_id, $sisow_merchant_key;
		global $store_name;
		global $api_use_https, $api_host, $api_root;
		
		$shop_id = "dibs"; // To be implemented by Sisow
		$amount = round( 100 * Cart::total_amount( $cart_id, "EUR" ) );
		$unique_code = "TODO"; // TODO
		
		$api_base = ( $api_use_https ? "https" : "http" ) . "://" . $api_host . $api_root;
		
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
		
		$response = self::request( "TransactionRequest", $query );
		if ( !$response ) return false;
		
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
	
	
	public static function verify_payment( $cart_id, $unique_code )
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
		
		$query = array(
			"trxid" => $trxid,
			"shopid" => $shop_id,
			"merchantid" => $sisow_merchant_id,
		);
		
		$pt = $trxid . $shop_id . $sisow_merchant_id . $sisow_merchant_key;
		$query["sha1"] = sha1($pt);
		
		$response = self::request( "StatusRequest", $query );
		if ( !$response ) return false;
		
		return array(
			"status" => strtolower( $response->transaction->status ),
			"issuer" => (string)$response->transaction->issuerid,
			"amount" => (float)$response->transaction->amount / 100,
		);
	}
}

