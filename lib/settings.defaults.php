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
	Default settings. 
	
	DO NOT EDIT THIS FILE.
	To change your settings, create or edit the "settings.php" file in your 
	main dibs folder.
*/



// The store name
$store_name = "Unnamed dibs store";
$store_email = "store@example.org";


// The server (address and/or port) for mongodb, if not equal to localhost
# $mongo_server = "mongo.example.org";


// The database collection for the ticketing system
$mongo_store = "dibs";


// The hostname where the dibs API is located
$api_host = "dibs.example.org";
// The location of the dibs API, relative to your site root
$api_root = "/dibs/";
// Indicates whether or not dibs communicates over HTTPS. Though the default is
// to not use HTTPS, it is strongly recommended that you do!
$api_use_https = false;



// The hashing algorithm used for storing passwords
$hash_algo = "sha256";
// The number of times to run this algorithm
$hash_count = 50000;

/**
 * $hash_algo and $hash_count should be tweaked such that generating a hash for
 * a password takes about half a second on commodity hardware.
 * Doing so greatly reduces vulnerability to rainbow tables.
 **/



// The default MIME type of the JSON output.
// The recommended value is "application/json" (RFC 4627), but "text/plain" is
// also a common value for debugging porpoises.
$content_type = "application/json";



// Merchant ID/key for Sisow iDeal.
$sisow_merchant_id = null;
$sisow_merchant_key = null;

// Enable a fake test bank to debug the store without draining one's bank account.
// Not recommended for production use.
$enable_test_bank = false;


// The default location for the invoice template.
$template_file = dirname(__FILE__) . "/../invoice-template.html";

// The default commands to postprocess an invoice HTML file.
// input and output files can be specified using the {if} and {of} parameters.
// ex.   "/bin/convert-to-pdf  -i {if} -o {of}"
$postprocess_invoice = array();


// The URL to the thusfar unpublished e-mail webservice I'm working on.
$email_webservice = "http://localhost:8064/mail";

// CC each invoice to this address, or comma-separated list of addresses:
$invoice_cc = false;



