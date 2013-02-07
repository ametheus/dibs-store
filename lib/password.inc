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
	Password authentication library
*/

require_once( "lib/mongo.inc" );


class Password
{
	public function authenticate( $username, $password )
	{
		$rv0 = true;
		
		$ui = self::get_user_info( (string)$username );
		if ( !$ui )
		{
			$rv0 = false;
			$ui = array( "password" => "hunter2", "salt" => "Worst movie in ages." );
		}
		
		$rv1 = self::constant_compare(
			self::get_hash( $password, $ui["salt"] ),
			$ui["password"]
		);
		return $rv1 && $rv0;
	}

	private function constant_compare( $str1, $str2 )
	{
		// By making use of a constant time comparison function, we render a timing
		// attack impossible.
		
		$rv = ( strlen($str1) == strlen($str2) );
		
		// Make both input strings the same length
		$str1 = str_pad( $str1, strlen($str2) );
		$str2 = str_pad( $str2, strlen($str1) );
		
		for ( $i = 0; $i < strlen($str1); $i++ )
			$rv = $rv && ( $str1[$i] == $str2[$i] );
		
		return $rv;
	}

	private function get_hash( $string, $salt )
	{
		global $hash_algo, $hash_count;
		
		$rv = $string . $salt;
		
		for ( $i = 0; $i < max($hash_count,1); $i++ )
		{
			$rv = hash( $hash_algo, $rv, true );
		}
		
		return base64_encode( $rv );
	}

	private function random_salt( $length = 12 )
	{
		$rv = "";
		$o = 3 * ceil( $length / 4 );
		
		for ( $i = 0; $i < $o; $i++ )
			$rv .= chr(mt_rand(0,255));
		
		return substr( base64_encode($rv), 0, $length );
	}




	public function get_user_info( $username )
	{
		$u = db()->{'admin_users'}->find(array("username" => (string)$username));
		
		if ( $u->count() == 0 ) return false;
		if ( $u->count() > 1 ) throw new Exception( "Non-unique username [{$username}]" );
		
		return $u->getNext();
	}

	public function list_users()
	{
		$rv = array();
		$u = db()->{'admin_users'}->find( array(), array("username" => true) );
		while ( $u->hasNext() )
		{
			$usr = $u->getNext();
			$rv[] = $usr["username"];
		}
		
		return $rv;
	}

	public function add_user( $username, $password )
	{
		if ( is_array(self::get_user_info($username)) )
			throw new Exception( "User [{$username}] already exists." );
		
		$username = (string)$username;
		$user = array( "username" => $username );
		
		$pw = self::new_password( $password );
		$user["salt"]     = $pw["salt"];
		$user["password"] = $pw["password"];
		
		db()->{'admin_users'}->insert($user);
		return $user;
	}

	public function delete_user( $username )
	{
		db()->{'admin_users'}->remove(array("username" => (string)$username));
		return true;
	}

	public function set_password( $username, $password )
	{
		return db()->{'admin_users'}->update(
			array("username" => (string)$username),
			array('$set' => self::new_password($password))
		);
	}

	private function new_password( $password )
	{
		$password = (string)$password;
		$salt = self::random_salt();
		
		return array(
			'salt' => $salt,
			'password' => self::get_hash( $password, $salt )
		);
	}



	private function normalize_permission_name( $perm )
	{
		return str_replace( array("."," "), "", $perm );
	}
	
	public function has_permission( $username, $permission )
	{
		$permission = self::normalize_permission_name( $permission );
		
		$u = db()->{'admin_users'}->find(
			array( "username" => (string)$username ),
			array( "permissions.{$permission}" => true )
		);
		
		if ( !$u->hasNext() ) return false;
		$u = $u->getNext();
		return @$u["permissions"][$permission];
	}
	public function grant_permission( $username, $permission )
	{
		$permission = self::normalize_permission_name( $permission );
		
		return db()->{'admin_users'}->update(
			array( "username" => (string)$username ),
			array( "\$set" => array( "permissions." . $permission => true ) )
		);
	}
	public function revoke_permission( $username, $permission )
	{
		$permission = self::normalize_permission_name( $permission );
		
		return db()->{'admin_users'}->update(
			array( "username" => (string)$username ),
			array( "\$unset" => array( "permissions." . $permission => true ) )
		);
	}
}


if ( basename(@$argv[0]) == basename(__FILE__) )
{
	require_once( "lib/termcolours.inc" );
	
	function fail( $x, $inverted = false )
	{
		if ( !$inverted )
			if ( $x )
				print( green("success") . "\n" );
			else
				print( red("failure") . "\n" );
		else
			if ( $x )
				print( red("unexpected success") . "\n" );
			else
				print( green("failure") . " as planned\n" );
	}
	
	print( "Testing password mechanism.\n\n" );
	
	print( "   Checking if user 'yubama' currently exists... " );
	if ( Password::get_user_info('yubama') )
	{
		print( yellow("yes. He might not like what we're about to do.") . "\n" );
		Password::delete_user('yubama');
	}
	else
	{
		print( green("no.") . "\n" );
	}
	
	print( "   Adding user 'yubama' with password 'spanklicious'... " );
	// How'd I come up with this?
	fail( Password::add_user( 'yubama', 'spanklicious' ) );
	print( "   Checking if he's really there...  " );
	$lu = Password::list_users();
	fail( in_array( 'yubama', $lu ) );
	
	print( "\n" );
	
	print( "   Authenticating  'yubama'    with password  'spanklicious' ...  " );
	fail( Password::authenticate( 'yubama', 'spanklicious' ) );
	print( "   Authenticating  'yubama'    with password  'espinoza'     ...  " );
	fail( Password::authenticate( 'yubama', 'espinoza' ), true );
	print( "   Authenticating  'theodore'  with password  'spanklicious' ...  " );
	fail( Password::authenticate( 'theodore', 'spanklicious' ), true );
	print( "   Changing        'yubama's   password to    'espinoza'     ...  " );
	fail( Password::set_password( 'yubama', 'espinoza' ) );
	print( "   Authenticating  'yubama'    with password  'espinoza'     ...  " );
	fail( Password::authenticate( 'yubama', 'espinoza' ) );
	print( "   Authenticating  'yubama'    with password  'spanklicious' ...  " );
	fail( Password::authenticate( 'yubama', 'spanklicious' ), true );
	
	print( "\n" );
	
	print( "   Checking if yubama has the  'wheeze'   permission ...  " );
	fail( Password::has_permission( 'yubama', 'wheeze' ), true );
	print( "   Granting yubama the         'wheeze'   permission ...  " );
	fail( Password::grant_permission( 'yubama', 'wheeze' ) );
	print( "   Checking if yubama has the  'wheeze'   permission ...  " );
	fail( Password::has_permission( 'yubama', 'wheeze' ) );
	print( "   Checking if yubama has the  'cough'    permission ...  " );
	fail( Password::has_permission( 'yubama', 'cough' ), true );
	print( "   Revoking yubama's           'wheeze'   permission ...  " );
	fail( Password::revoke_permission( 'yubama', 'wheeze' ) );
	print( "   Checking if yubama has the  'wheeze'   permission ...  " );
	fail( Password::has_permission( 'yubama', 'wheeze' ), true );
	
	print( "\n" );
	
	print( "   Deleting user 'yubama' ...  " );
	fail( Password::delete_user('yubama') );
	print( "   Checking if he's really gone ...  " );
	fail( !is_array(Password::get_user_info('yubama')) );
	print( "   Checking if the number of users decreased just now ...  " );
	fail( (count($lu) - count(Password::list_users())) == 1 );
	
	
	print( "\nDone.\n" );
}
