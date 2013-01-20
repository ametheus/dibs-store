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
	Central request handler.
*/

header( "Access-Control-Allow-Origin: *" );
ob_start();

register_shutdown_function("shutdown");

require_once( "lib/settings.defaults.php" );
@include_once( "settings.php" );

require_once( "lib/mongo.inc" );


// Find out the API version from the request URI
$uri = substr( $_SERVER["REQUEST_URI"], strlen($api_root) );
list($uri) = explode( "?", $uri );

if ( $uri == "versions" )
{
	$op = array(
		"application" => array(
			"name" => "dibs-store",
			"version" => "0.4.0-0.2.0",
		),
		"API-versions" => array(
			"1" => "0.4.0-0.2.0"
		),
	);
	output_json( 0, $op );
}
elseif ( substr($uri,0,2) == "1/" )
{
	// This is an API version 1 call; proceed accordingly.
	
	$output = null;
	
	require_once( "v1/manager.php" );
	
	$status = handle_request( substr($uri,2), $output );
	output_json( $status, $output );
}
elseif ( preg_match( '#^h[ea]lp/(\d+)/?$#', $uri, $A ) )
{
	// Display help for this error
	
	require_once( "lib/help.inc" );
	$halp = Help::error_info( (int)$A[1] );
	
	output_json( $halp == null ? 1 : 0, $halp );
}





// Could not find a matching address. Exit with an error.

output_json( 1, array(
	"error" => "Unknown API call.",
	"additional information" => array(
		"URI" => $uri,
		"query string" => $_GET,
		"API root" => $api_root,
		"translated URI" => $uri,
	),
));





/**
 * Basically, encode $output as JSON, print it to screen, and exit.
 */
function output_json( $status, $output = null )
{
	global $content_type;
	
	// Great. Five minutes in development, and already my strange adherence to
	// unixey conventions is making this a candidate for a feature on
	// thedailywtf.com
	if ( $status === true ) $status = 0;
	if ( $status === false ) $status = 1;
	if ( $status === null ) $status = 1;
	
	$status = (int)$status;
	$shell = ob_get_clean();
	
	$rv = array(
		"status" => $status,
	);
	
	if ( $output !== null )
		$rv["output"] = $output;
	
	
	// Add a short error message to the output
	if ( $status != 0 )
	{
		require_once( "lib/help.inc" );
		$http = Help::http_status( $status );
		$error = Help::error_info( $status );
		if ( $error )
		{
			$error = $error["short"];
			global $api_host, $api_root, $api_use_https;
			
			$rv["error"] = $error;
			$rv["see_also"] = ( $api_use_https ? "https://" : "http://" ) . 
				$api_host . $api_root . "help/{$status}";
			
		}
		else
		{
			$error = "I am a teapot";
		}
		header( "HTTP/1.1 {$http} {$error}" );
	}
	
	
	if ( strlen($shell) > 0 )
		$rv["shell-messages"] = $shell;
	
	header( "Content-type: {$content_type}; charset=UTF-8" );
	
	
	// Remove any MongoDB object id's
	sanitize_mongo_objects( $rv );
	
	
	if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 )
	{
		// PHP 5.4 correctly handles unicode strings in JSON by itself.
		print( json_encode( $rv, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}
	else
	{
		// Sadly, earlier versions need a bit of help
		$rv = json_encode( $rv );
		$rv = str_replace( 
			array( "\\",   "\\\\\"", "\"",   ),
			array( "\\\\", "\\\"",   "\\\"", ),
			$rv
		);
		print( json_decode( "\"{$rv}\"" ) );
	}
	print( "\n" );
	
	exit( $status );
}


/** 
 * Scrub an object for output.
 * 
 * Remove the MongoDB-specific "_id" attribute in this array
 * Convert MongoDate objects to a human-readable string.
 **/
function sanitize_mongo_objects( &$arr )
{
	$keys = array_keys( $arr );
	
	foreach ( $keys as $k )
	{
		if ( $k == "_id" && is_object($arr[$k]) )
			unset( $arr[$k] );
		elseif ( is_a( $arr[$k], "MongoDate" ) )
			$arr[$k] = date('Y-m-d H:i:s.', $arr[$k]->sec ) . $arr[$k]->usec;
		elseif ( is_array($arr[$k]) )
			sanitize_mongo_objects( $arr[$k] );
	}
}


function shutdown()
{
	$er = error_get_last();
	if ( $er["type"] == E_ERROR )
	{
		output_json( 1 );
	}
}


