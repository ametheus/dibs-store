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
	Database connectivity.
*/

require_once( "lib/settings.defaults.php" );
@include_once( "settings.php" );


function db()
{
	static $db = null;
	
	if ( $db == null )
	{
		$mon = false;
		
		global $mongo_server, $mongo_store;
		
		if ( isset($mongo_server) )
			$mon = new Mongo($mongo_server);
		else
			$mon = new Mongo();
		
		
		$db = $mon->{$mongo_store};
	}
	
	return $db;
}


function get_counter( $counter_name )
{
	$rv = db()->counters->findOne( array( "_id" => (string)$counter_name ) );
	if ( !$rv ) return null;
	
	return $rv["value"];
}
function inc_counter( $counter_name )
{
	$rv = get_counter( $counter_name );
	if ( !$rv ) return null;
	
	db()->counters->update(
		array( "_id" => (string)$counter_name ),
		array( '$inc' => array( "value" => 1 ) )
	);
	return $rv;
}
