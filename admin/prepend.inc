<?php

// Set include paths
$ip = explode( PATH_SEPARATOR, ini_get("include_path") );

$q = array_shift($ip);

array_unshift( $ip, realpath(dirname(__FILE__)."/..") );
if ( $q != "." ) array_unshift( $ip, $q );
array_unshift( $ip, "." );

ini_set( "include_path", implode( PATH_SEPARATOR, $ip ) );

unset( $ip, $q );



require_once( "lib/mongo.inc" );
require_once( "lib/password.inc" );


if ( ! Password::authenticate( @$_SERVER["PHP_AUTH_USER"], @$_SERVER["PHP_AUTH_PW"] ) )
{
	define( "AUTH_USER", null );
	
	header( 'WWW-Authenticate: basic realm="dibs"' );
    header( 'HTTP/1.1 401 Unauthorized' );
    
    print( "If you didn't call dibs, you have no right to be here." );
    exit;
}

define( "AUTH_USER", $_SERVER["PHP_AUTH_USER"] );
$USER_INFO = Password::get_user_info( AUTH_USER );



