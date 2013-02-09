<?php

header( "Content-type: application/json; charset=UTF-8" );

$status = array();

foreach ( $_REQUEST as $k => $v )
{
	$k = str_replace( array(" ",".",'$'), "", $k );
	$v = $v == 0 ? 0 : 1;
	
	$status["status.{$k}"] = array('$exists'=>$v);
}


if ( count($status) == 0 ) die( "[]" );

$cur = db()->carts->find($status)->sort(array("invoice-no" => 1, "created" => 1));
$rv = array();

while ( $cur->hasNext() )
	$rv[] = $cur->getNext();

print( _json_encode($rv) );
