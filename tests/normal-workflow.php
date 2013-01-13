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
$ean = "1936013030903";
$another_ean = "1936013030927";


print( "Creating a new cart\n" );

$o = dibs::req( "1/cart/" );
$cart_id = $o["cart-id"];

print( "Cart ID: [{$cart_id}]\n\n" );


print( "Ordering a [{$ean}]... " );
$o = dibs::req( "1/cart/{$cart_id}", array(), array( "EAN" => $ean, "count" => 1 ) );
print(  "done. Total [{$o["items"][1]["count"]}]\n" );


print( "Ordering another [{$ean}]... " );
$o = dibs::req( "1/cart/{$cart_id}", array(), array( "EAN" => $ean, "count" => 1 ) );
print(  "done. Total [{$o["items"][1]["count"]}]\n" );


print( "No wait, i'm ordering 5 instead... " );
$o = dibs::req( "1/cart/{$cart_id}/1", array(), array( "count" => 5 ) );
print(  "done. Total [{$o["items"][1]["count"]}]\n\n" );


print( "Also ordering two [{$another_ean}]s... " );
$o = dibs::req( "1/cart/{$cart_id}", array(), array( "EAN" => $another_ean, "count" => 2 ) );
print(  "done. Total [{$o["items"][2]["count"]}]\n" );


print( "That's it; all done... " );
$addr = array(
		"email" => "bogus e-mail address",
		
		"del-name" => "Téßtpersoon 维基百科",
		"del-street1" => "t.a.v. Mark Rutte",
		"del-street2" => "Adriaan Goekooplaan 10",
		"del-postcode" => "2517 JX",
		"del-city" => "Den Haag",
		"del-country" => "Neder;and",
	);
try
{
	$o = dibs::req( "1/confirm/{$cart_id}", array(), $addr );
}
catch ( Exception $e )
{
	print( "whoops, let's try that again... " );
}

$addr["email"] = "premier@geheimeinformatie.nl";
$o = dibs::req( "1/confirm/{$cart_id}", array(), $addr );

print( "order submitted.\n" );
print_r($o);



print( "\n\nPaying with iDEAL.\n" );
$banks = dibs::req( "1/ideal/issuers" );
foreach ( $banks as $b )
	print( "   * {$b['name']}\n" );
$bank_id = false;
while ( ! $bank_id )
{
	print( "Please enter the name of one of the banks above: " );
	$bank = fgets(STDIN);
	foreach ( $banks as $b )
		if ( strtolower(trim($b["name"])) ==  strtolower(trim($bank)) )
			$bank_id = $b["id"];
}




$o = dibs::req( "1/ideal/pay/{$bank_id}/{$cart_id}", array( "return-url" => "http://www.tweakers.net/" ) );
print( "\nRedirect to <" . $o["redirect-url"] . ">\n\n" );

print( "Why don't you just go ahead and, um, do that, and I'm gonna go ahead and, 
	um, wait here until you get back. M'kay?\n" );
fgets(STDIN);


print( "This is what it looks like now:\n" );
$o = dibs::req( "1/cart/{$cart_id}" );
print_r($o);

