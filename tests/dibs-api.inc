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
	PHP wrapper for the dibs v1 API
*/


class dibs
{
	
	private static $https;
	private static $host;
	private static $port;
	private static $root;
	
	public static function setup( $host, $root, $https = true, $port = null )
	{
		self::$https = $https;
		self::$host = $host;
		self::$port = $port;
		self::$root = $root;
	}
	
	protected static function url()
	{
		return ( self::$https ? "https" : "http" ) .
			"://" . self::$host .
			( self::$port ? ":" . self::$port : "" ) .
			self::$root;
	}
	
	public static function req( $url, $query = array(), $post_fields = null )
	{
		$url = self::url() . $url;
		if ( count($query) )
			$url .= "?" . http_build_query( $query );
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		
		if ( is_array($post_fields) )
		{
			curl_setopt( $ch, CURLOPT_POST, count($post_fields) );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post_fields ) );
		}
		
		$result = curl_exec( $ch );
		$rv = @json_decode( $result, 1 );
		curl_close($ch);
		
		
		if ( !is_array($rv) )
			throw new Exception( "Request error: [{$result}] is invalid JSON." );
		
		if ( $rv["status"] != 0 )
			throw new Exception( "API error " . $rv["status"] . ": " . @$rv["error"] );
		
		return $rv["output"];
	}
}

