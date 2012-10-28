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
	At least one admin user should exist, and at least one super admin.
*/



require_once( "test.base.inc" );
require_once( "lib/password.inc" );


function ask_for_username()
{
	global $db;
	
	$un = "";
	while ( ! preg_match('/^\w{3,}$/', $un) )
		$un = trim(readline("Enter username: "));
	
	//print( "Checking if [{$un}] exists...\n" );
	
	if ( !get_user_info($un) )
	{
		$pw = trim(readline("Enter a password for user [{$un}]: "));
		add_user( $un, $pw );
	}
	
	return $un;
}
function promote_to_super_admin( $username )
{
	user_grant_permission( $username, 'root' );
}



class AdminUsers extends TestBase
{
	public function get_dependencies()
	{
		return array();
	}
	
	public $test_title = "Checking for usernames for the admin panel";
	
	public function perform_test()
	{
		return count(list_users()) > 0;
	}
	public $recoverable = true;
	
	public function fix_it()
	{
		print( "No users found. Please create a new user.\n" );
		$un = ask_for_username();
		promote_to_super_admin( $un );
		return true;
	}
}

class OneSuperUser extends TestBase
{
	public function get_dependencies()
	{
		return array( "AdminUsers" );
	}
	
	public $test_title = "Checking if at least one user is a 'super admin'";
	
	public function perform_test()
	{
		global $db;
		
		$u = $db->{'admin_users'}->find( array("permissions.root" => true), array() );
		
		return $u->count() > 0;
	}
	public $recoverable = true;
	
	public function fix_it()
	{
		print( "No admin users found.\n" );
		print( "Please specify which user to promote to super admin, or " .
			"enter a new user to create one.\n" );
		print( "Existing users:  " );
		$lu = list_users();
		sort($lu);
		foreach ( $lu as $u )
			print( "  " . purple($u) );
		print( "\n" );
		
		$un = ask_for_username();
		promote_to_super_admin( $un );
		
		return true;
	}
}


