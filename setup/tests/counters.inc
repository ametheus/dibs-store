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
	Ensure that the "counters" collection exists.
*/


class CountersCollection extends TestBase
{
	public function get_dependencies()
	{
		return array();
	}
	
	public $test_title = "Checking if the right counters are present";
	
	protected $counters = array( "invoice-no" );
	
	public function perform_test()
	{
		foreach ( $this->counters as $c )
		{
			$p = db()->counters->findOne( array("_id" => (string)$c ) );
			if ( !$p ) return false;
		}
		return true;
	}
	public $recoverable = true;
	
	public function fix_it()
	{
		foreach ( $this->counters as $c )
		{
			$c = (string)$c;
			$p = db()->counters->findOne( array("_id" => $c ) );
			if ( !$p )
			{
				print( "Counter {$c} not present; setting it to zero.\n" );
				db()->counters->insert( array("_id" => $c, "value" => 0 ) );
			}
		}
		return true;
	}
}


