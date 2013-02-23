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


session_start();

if ( !empty($_SESSION["username"]) )
{
	// User already logged in
	define( "AUTH_USER", $_SESSION["username"] );
}
elseif ( !empty($_POST["username"]) && !empty($_POST["password"]) 
	&& Password::authenticate( $_POST["username"], $_POST["password"] ))
{
	// User just logged in. Store the username in the session.
	
	$_SESSION["username"] = $_POST["username"];
	define( "AUTH_USER", $_SESSION["username"] );
}
else
{
	// User not logged in. Redirect to the login page, and deny access.
	define( "AUTH_USER", false );
	
	$continue = $_SERVER["REQUEST_URI"];
	list($cf) = explode("?",$continue);
	
	// Of course, allow access to the login page.
	if ( $cf != "{$api_root}admin/login.php" )
	{
		header( "HTTP/1.1 302 See Other" );
		header( "Location: {$api_root}admin/login.php?continue=" . urlencode($continue) );
		
		print( "If you didn't call dibs, you have no right to be here." );
		exit;
	}
	unset( $continue, $cf );
}

session_write_close();
$USER_INFO = Password::get_user_info( AUTH_USER );



