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
	Terminal colours
*/


// 'Normal' colours:
function black($s)       { return _term_colour( '0;30', $s ); }
function blue($s)        { return _term_colour( '0;34', $s ); }
function green($s)       { return _term_colour( '0;32', $s ); }
function cyan($s)        { return _term_colour( '0;36', $s ); }
function red($s)         { return _term_colour( '0;31', $s ); }
function purple($s)      { return _term_colour( '0;35', $s ); }
function yellow($s)      { return _term_colour( '0;33', $s ); }
function white($s)       { return _term_colour( '0;37', $s ); }

// Bright or bold colours:
function bblack($s)      { return _term_colour( '1;30', $s ); }
function bblue($s)       { return _term_colour( '1;34', $s ); }
function bgreen($s)      { return _term_colour( '1;32', $s ); }
function bcyan($s)       { return _term_colour( '1;36', $s ); }
function bred($s)        { return _term_colour( '1;31', $s ); }
function bpurple($s)     { return _term_colour( '1;35', $s ); }
function byellow($s)     { return _term_colour( '1;33', $s ); }
function bwhite($s)      { return _term_colour( '1;37', $s ); }

function _term_colour( $colourcode, $string )
{
	$e = chr(27);
	
	return "{$e}[{$colourcode}m{$string}{$e}[0m";
}





if ( basename(@$argv[0]) == basename(__FILE__) )
{
	print( "Testing terminal colours.\n\n" );
	
	$colours = array("black","blue","green","cyan","red","purple","yellow","white");
	$op = "";
	
	foreach ( $colours as $f )
		$op .= $f( "{$f}  " );
	
	$op .= "\n";
	
	foreach ( $colours as $f )
	{
		$f = "b".$f;
		$op .= $f( "{$f} " );
	}
	
	print( "{$op}\n\nDone.\n" );
}
