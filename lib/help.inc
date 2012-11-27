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
	Built-in help information
*/

class Help
{
	/**
	 * Return information about an error that was thrown.
	 **/
	static function error_info( $errno )
	{
		// Rather than loading the entire error list in memory, a simple switch
		// statement is more appropriate
		switch ( $errno )
		{
			case 0: return array(
				"short" => "Everything went fine.",
				"long" => "The API request succeeded. It may not have resulted " .
					"in exactly what you wanted, but status 0 generally means " .
					"the API did its job.",
				"versions" => array(1),
			);
			case ( 1 ): return array(
				"short" => "Internal Server Error",
				"long" => "An error occurred that wasn't assigned an " .
					"error number during development of this API.\n" .
					"If you see this error, it is likely that more " .
					"things have gone wrong than is directly visible.",
				"versions" => array(1),
			);
			case ( 2 ): return array(
				"short" => "Unknown API call",
				"long" => "A call was made to an unrecognized address.",
				"versions" => array(1),
			);
			case ( 3 ): return array(
				"short" => "Cart does not exist",
				"long" => "No cart with the given cart ID exists.\n" .
					"Either the cart ID is wrong, or the cart has been marked as expired.",
				"versions" => array(1),
			);
			case ( 4 ): return array(
				"short" => "This product does not exist.",
				"long" => "You've requested a product with an unknown EAN.",
				"versions" => array(1),
			);
			case ( 5 ): return array(
				"short" => "Cart has been marked closed.",
				"long" => "You have either completed purchase, or the cart has " .
					"been marked as expired. Eitherway, it is no longer possible " .
					"to add or remove products.",
				"versions" => array(1),
			);
		}
		return false;
	}
	
	
	/**
	 * For each error number, return the correct HTTP response code
	 **/
	static function http_status( $errno )
	{
		switch ( $errno )
		{
			case ( 1 ): return 500;
			case ( 2 ): return 400;
			case ( 3 ): return 404;
			case ( 4 ): return 404;
			case ( 5 ): return 403;
		}
		
		// If any other error occurs, I must be a teapot:
		return 418;
	}
}