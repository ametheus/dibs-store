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
		Cart::add_status( $cart_id, "invoice-assigned" );
		
		return $inv;
	}
	
	
	/**
	 * Create an invoice document, and return the file name.
	 **/
	static function generate_invoice( $cart, $billing_address = true, $copy = false )
	{
		global $template_file, $postprocess_invoice;
		if ( !file_exists($template_file) )
			return "/dev/null";
		
		// $cart can either be a cart object, or a cart ID.
		if ( is_string( $cart ) )
			$cart = Cart::get( $cart, $short = 0 );
		
		ob_start();
		include( $template_file );
		$html = ob_get_contents();
		ob_end_clean();
		
		$if = tempnam( "/tmp", "dibs-" );
		file_put_contents( $if, $html );
		
		foreach ( $postprocess_invoice as $cmd )
		{
			$of = tempnam( "/tmp", "dibs-" );
			$cmd = str_replace( array('{if}', '{of}'),
				array( escapeshellarg($if),escapeshellarg($of)), $cmd );
			
			shell_exec( $cmd );
			unlink( $if );
			$if = $of;
		}
		
		return $if;
	}
	
	
	/**
	 * Send an e-mail with an invoice file.
	 **/
	static function email_invoice( $cart_id )
	{
		global $store_name, $store_email, $invoice_cc;
		
		$cart = Cart::get( $cart_id, $short = 0 );
		$file = self::generate_invoice( $cart, $billing = true, $copy = true );
		
		$att = array(
			"factuur-{$cart["invoice-no"]}.pdf" => base64_encode(file_get_contents($file)),
		);
		unlink( $file );
		
		// TODO: Make this somehow configurable.
		$mail = array(
			"to" => $cart["email"],
			"from" => "{$store_name} <{$store_email}>",
			"subject" => "{$store_name}: order {$cart['invoice-no']}",
			"mvg" => "de webwinkel",
			"body" => "
				<p>Uw bestelling is geplaatst, en wordt zo spoedig mogelijk verzonden.</p>
				<p>Bijgaand vindt u de factuur voor deze order, voor uw administratie</p>
			",
			"attachments" => $att,
		);
		$rv = Email::pipe( $mail );
		
		if ( $invoice_cc )
		{
			$file = self::generate_invoice( $cart, $billing = false, $copy = false );
			
			$att = array(
				"factuur-{$cart["invoice-no"]}.pdf" => base64_encode(file_get_contents($file)),
			);
			unlink( $file );
			
			$cc = array(
				"to" => $invoice_cc,
				"from" => "{$store_name} <{$store_email}>",
				"subject" => "Kopie: {$store_name} order {$cart["invoice-no"]}",
				"aanhef" => "Hoi hoi",
				"mvg" => "de webwinkel",
				"body" => "
					<p>Zojuist is deze bestelling geplaatst.</p>
					<p>Gelieve 'm zo snel mogelijk te verwerken.</p>
				",
				"attachments" => $att,
			);
			Email::pipe( $cc );
		}
		
		if ( $rv )
			Cart::add_status( $cart_id, "invoice-sent" );
		
		return $rv;
	}
}
