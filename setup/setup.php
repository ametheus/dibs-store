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
	Set up script. Basically an implementation of Makefiles using PHP.
*/



// Add the parent dir to the include path, for consistency.
set_include_path( get_include_path() . PATH_SEPARATOR . ".." );

require_once( "lib/termcolours.inc" );

if ( !isset($argv) )
{
	print( "\n[" . red("WARNING") . "]: This script should be run from the CLI!\n" );
	die( "If you can see this message in a web browser, lots of other things " .
		"have gone terribly wrong.\n\n" );
}



require_once( "test.base.inc" );

$tests = array();

// Find all tests in the tests dir, and put them in the array above
$files = scandir( "./tests" );
foreach ( $files as $f )
{
	if ( @substr($f,-4) != ".inc" ) continue;
	
	$c = file_get_contents( "./tests/{$f}" );
	preg_match_all( '/class\s+(\w+)\s+extends\s+TestBase/', $c, $A );
	
	if ( count($A[1]) == 0 ) continue;
	
	include_once( "./tests/{$f}" );
	
	foreach ( $A[1] as $classname )
		if ( class_exists( $classname ) )
			$tests[$classname] = new $classname;
}


// Build a dependency tree
$deps = array();
$todo = array_keys( $tests );

function try_to_add( $classname )
{
	global $tests;
	global $deps;
	global $progress;
	
	$rv = true;
	
	if ( in_array( $classname, $deps ) )
		return true;
	
	if ( !array_key_exists( $classname, $tests ) )
	{
		print( "[" . red("ERROR") . "]: dependency not met: [" .
			yellow($classname) . "]\n" );
		exit(1);
	}
		
	
	$c = $tests[$classname];
	$d = $c->get_dependencies();
	
	foreach ( $d as $dp )
		$rv = $rv && try_to_add($dp);
	
	$progress = $progress || $rv;
	
	if ( $rv )
		array_push( $deps, $classname );
	
	return $rv;
}

$progress = true;
while ( count($todo) > 0 )
{
	if ( !$progress )
	{
		print( "[" . red("ERROR") . "]: dependency loop found" );
	}
	$progress = false;
	
	$cn = array_pop( $todo );
	if ( !try_to_add($cn) )
		array_unshift( $todo, $cn );
}



function special_ellipsis( $str, $length )
{
	if ( strlen($str) > $length )
		return substr( $str, $length - 3 ) . "...";
	
	return str_pad( $str, $length );
}

foreach ( $deps as $d )
{
	$c = $tests[$d];
	
	@list($height,$width) = explode(" ",shell_exec("stty size 2>/dev/null"));
	
	$width = max( $width - 9, 71 );
	
	print( special_ellipsis( $c->test_title, $width ) );
	
	if ( !$c->perform_test() )
	{
		$b = $c->recoverable;
		
		if ( $b )
		{
			print( "  [" . yellow("WARN") . "]" );
			print( "\n" );
			
			$b = $c->fix_it();
			
			print( str_repeat(" ",$width) );
		}
		
		if ( !$b )
		{
			print( "  [" . red("FAIL") . "]\n" );
			exit( 1 );
		}
	}
	
	print( "  [" . green(" OK ") . "]\n" );
}

