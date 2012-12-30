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
	Example of a normal workflow
*/

$cwd = dirname(__FILE__);
require_once( "{$cwd}/../lib/settings.defaults.php" );
@include_once( "{$cwd}/../settings.php" );
require_once( "{$cwd}/dibs-api.inc" );

dibs::setup( $api_host, $api_root, $api_use_https, null );


// TODO: something fancier.
$ean = "1936000000001";


print( "Creating a new cart\n" );

$o = dibs::req( "1/cart/" );
$cart_id = $o["cart-id"];

print( "Cart ID: [{$cart_id}]\n\n" );


print( "Ordering a [{$ean}]... " );
$o = dibs::req( "1/cart/{$cart_id}", array(), array( "EAN" => $ean, "count" => 1 ) );
print(  "done. Total [{$o["items"][0]["count"]}]\n" );


print( "Ordering another [{$ean}]... " );
$o = dibs::req( "1/cart/{$cart_id}", array(), array( "EAN" => $ean, "count" => 1 ) );
print(  "done. Total [{$o["items"][0]["count"]}]\n" );


print( "No wait, i'm ordering 5 instead... " );
$o = dibs::req( "1/cart/{$cart_id}/0", array(), array( "count" => 5 ) );
print(  "done. Total [{$o["items"][0]["count"]}]\n\n" );


print( "That's it; all done.\n" );
$o = dibs::req( "1/confirm/{$cart_id}", array(),
	array(
		"del-name" => "Testpersoon",
		"del-street" => "t.a.v. Mark Rutte",
		"del-street1" => "Adriaan Goekooplaan 10",
		"del-postcode" => "2517 JX",
		"del-city" => "Den Haag",
		"del-country" => "Neder;and",
	) );
print_r($o);


print( "Paying with iDEAL.\n" );
$o = dibs::req( "1/ideal/pay/{$cart_id}", array( "return-url" => "http://www.tweakers.net/" ) );
print( "Redirect to <" . $o["redirect-url"] . ">\n\n" );

print( "Why don't you just go ahead and, um, do that, and I'm gonna go ahead and, 
	um, wait here until you get back. M'kay?\n" );
fgets(STDIN);


print( "This is what it looks like now:\n" );
$o = dibs::req( "1/cart/{$cart_id}" );
print_r($o);

