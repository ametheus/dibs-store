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


list($script_name) = explode( "?", $_SERVER["REQUEST_URI"] );

session_start();

if ( $script_name == "{$api_root}admin/logout.php" )
{
	define( "AUTH_USER", false );
	unset($_SESSION['username']);
	session_destroy();
	
	header( "HTTP/1.1 302 See Other" );
	header( "Location: {$api_root}admin/" );
	
	print( "You have been successfully logged out." );
	exit;
}
elseif ( !empty($_SESSION["username"]) )
{
	// User is logged in
	define( "AUTH_USER", $_SESSION["username"] );
}
elseif ( !empty($_POST["username"]) && !empty($_POST["password"]) 
	&& Password::authenticate( $_POST["username"], $_POST["password"] ))
{
	// User just logged in. Store the username in the session.
	
	session_regenerate_id(true);
	
	$_SESSION["username"] = $_POST["username"];
	define( "AUTH_USER", $_SESSION["username"] );
}
elseif ( $script_name == "{$api_root}admin/login.php" )
{
	// User not logged in, but trying to.
	
	define( "AUTH_USER", false );
	require_once( dirname(__FILE__) . "/login.php" );
	exit;
}
else
{
	// User not logged in. Redirect to the login page, and deny access.
	define( "AUTH_USER", false );
	
	$continue = $_SERVER["REQUEST_URI"];
	
	header( "HTTP/1.1 302 See Other" );
	header( "Location: {$api_root}admin/login.php?continue=" . urlencode($continue) );
	
	print( "If you didn't call dibs, you have no right to be here." );
	exit;
}

unset( $script_name );

session_write_close();
$USER_INFO = Password::get_user_info( AUTH_USER );



