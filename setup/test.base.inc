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
	Base class for "tests", or Make targets.
*/

abstract class TestBase
{
	public function get_dependencies()
	{
		return array();
	}
	
	public $test_title = "Checking if all test have a test title. (Apparently not.)";
	
	public abstract function perform_test();
	public $recoverable = false;
	
	public function fix_it()
	{
		throw new Exception("This test is specified as recoverable, however, " .
			"no recovery method was given.");
	}
}

