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

ob_start();

register_shutdown_function("shutdown");

require_once( "lib/settings.defaults.php" );
@include_once( "settings.php" );

require_once( "lib/mongo.inc" );


// Find out the API version from the request URI
$uri = substr( $_SERVER["REQUEST_URI"], strlen($api_root) );
list($uri) = explode( "?", $uri );

if ( substr($uri,0,2) == "1/" )
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
		$error = Help::error_info( $status );
		if ( $error )
		{
			$error = $error["short"];
			global $api_host, $api_root, $api_use_https;
			
			$rv["error"] = $error;
			$rv["see-also"] = ( $api_use_https ? "https://" : "http://" ) . 
				$api_host . $api_root . "help/{$status}";
			
			$http = Help::http_status( $status );
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
		print( json_utf8_encode( $rv ) );
	}
	print( "\n" );
	
	exit( $status );
}


/** 
 * Remove the MongoDB-specific "_id" attribute in this array
 **/
function sanitize_mongo_objects( &$arr )
{
	$keys = array_keys( $arr );
	
	foreach ( $keys as $k )
	{
		if ( $k == "_id" && is_object($arr[$k]) )
			unset( $arr[$k] );
		elseif ( is_array($arr[$k]) )
			sanitize_mongo_objects( $arr[$k] );
	}
}


function json_utf8_encode( $x, $numbers_as_strings = false, $indent = 0, $indent_with="  " )
{
	if ( is_string($x) || ( is_numeric($x) && $numbers_as_strings == true ) )
	{
		$x = str_replace(
			array( "\\",   "\"", "\r", "\n", "\t", "\0"),
			array("\\\\","\\\"","\\r","\\n","\\t","\\0"),
			$x
		);
		return "\"{$x}\"";
	}
	
	if ( is_numeric($x) )
		return $x;
	
	if ( $x === true )
		return "true";
	if ( $x === false )
		return "false";
	if ( $x === null )
		return "null";
	
	if ( !is_array($x) )
		return "null /* ".var_export($x,true)." */";
	
	
	$I = "\n" . str_repeat( $indent_with, $indent );
	$I1 = $I . $indent_with;
	$f = create_function('$a', 'return json_utf8_encode( $a, ' .
		( $numbers_as_strings ? 'true' : 'false' ) . 
		', ' . ($indent + 1) . ', "' . $indent_with . '" );');
	
	
	if ( is_numeric_array($x) )
		return "[{$I1}" . str_repeat( $indent_with, $indent ) . 
			implode( ",{$I1}",
				array_map( $f, $x ) ) .
		"{$I}]";
	
	// Else: non-numeric array.
	$rv = "";
	foreach ( $x as $k => $v )
		$rv .= ",{$I1}" . json_utf8_encode( $k, true ) . 
			": " . json_utf8_encode( $v, $numbers_as_strings, $indent + 1, $indent_with );
			
	if ( strlen($rv) )
		$rv = $I1 . substr( $rv, 1 + strlen($I1) ) . $I;
	return ( $indent == 0 ? "" : $I ) . "{{$rv}}";
}
function is_numeric_array( $a )
{
	if ( !is_array($a) ) return false;
	if ( count($a) == 0 ) return false;
	
	$l = count($a);
	for ( $i = 0; $i < $l; $i++ )
		if ( !array_key_exists( $i, $a ) )
			return false;
	return true;
}


function shutdown()
{
	$er = error_get_last();
	if ( $er["type"] == E_ERROR )
	{
		output_json( 1 );
	}
}


