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
	E-mail wrapper
*/


class Email
{
	/**
	 * Pipe an object to the thusfar unpublished e-mail webservice.
	 **/
	static function pipe( $arr )
	{
		global $email_webservice;
		if ( ! $email_webservice ) return false;
		
		$json = json_encode( $arr );
 
		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, $email_webservice );
		curl_setopt( $c, CURLOPT_USERAGENT, "Mail-wrapper" );
		curl_setopt( $c, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($json)) );
		curl_setopt( $c, CURLOPT_VERBOSE, false );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $c, CURLOPT_CUSTOMREQUEST, "PUT" ); 
		curl_setopt( $c, CURLOPT_POSTFIELDS, $json );
		curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, 0 );
		$c_result = curl_exec($c);
		$c_apierr = curl_errno($c);
		$c_errmsg = curl_error($c);
		curl_close($c);
		
		return ( trim($c_result) == "true" );
	}
}

