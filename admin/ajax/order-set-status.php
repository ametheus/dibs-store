<?php

require_once( "lib/cart.inc" );

$cart_id = (string)@$_REQUEST["cart-id"];
$status = (string)@$_REQUEST["status"];



var_dump( Cart::add_status( $cart_id, $status, $user = AUTH_USER ) );
