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

require_once( "lib/settings.defaults.php" );
@include_once( "settings.php" );

require_once( "lib/mongo.inc" );

print( "Dit is een gecontrolationeerde omgeving." );



output_json( 0, array("Yep"=>"Hij dóet het.") );


/**
 * Basically, encode $output as JSON, and print it to screen.
 */
function output_json( $status, $output = null )
{
	global $content_type;
	
	$status = (int)$status;
	$shell = ob_get_clean();
	
	$rv = array(
		"status" => $status,
	);
	
	if ( $output !== null )
		$rv["output"] = $output;
	
	if ( strlen($shell) > 0 )
		$rv["shell-messages"] = $shell;
	
	header( "Content-type: {$content_type}; charset=UTF-8" );
	
	if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 )
	{
		// PHP 5.4 correctly handles unicode strings in JSON by itself.
		print( json_encode( $rv, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	}
	else
	{
		// Sadly, earlier versions need a bit of help
		print( json_utf8_encode( $rv ) );
	}
	
	exit( $status );
}


function json_utf8_encode( $x, $numbers_as_strings = false, $indent = 0, $indent_with="  " )
{
	if ( $numbers_as_strings )
		if ( is_numeric($x) )
			return $x;
	
	if ( is_string($x) )
	{
		$x = str_replace(
			array( "\\",   "\"", "\n", "\t", "\0"),
			array("\\\\","\\\"","\\n","\\t","\\0"),
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
		$rv .= ",{$I1}" . json_utf8_encode($k) . 
			": " . json_utf8_encode( $v, $numbers_as_strings, $indent + 1, $indent_with );
			
	if ( strlen($rv) )
		$rv = $I1 . substr( $rv, 1 + strlen($I1) ) . $I;
	return "{{$rv}}";
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

