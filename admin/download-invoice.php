<?php

require_once( "lib/cart.inc" );
require_once( "lib/invoice.inc" );

$cart = Cart::get( (string)$_REQUEST["cart-id"], $short = 0 );


$billing_address = ! empty($_REQUEST["billing"]);
$f = Invoice::generate_invoice( $cart, $billing_address, $copy = false );


header( "Content-disposition: attachment; filename=invoice-{$cart["invoice-no"]}.pdf" );
header( "Content-type: application/pdf" );

readfile( $f );
unlink( $f );
