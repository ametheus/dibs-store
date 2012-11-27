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
	Handles .../1/product/... calls.
*/

require_once( "lib/item.inc" );



function handle_item_request( $uri, &$output )
{
	$verb = $_SERVER["REQUEST_METHOD"];
	
	
	/**
	 * GET .../1/product/{EAN}
	 *
	 * Get a single item description
	 **/
	if ( $verb == "GET" && preg_match( '#product/(\d+)/?#', $uri, $A ) )
	{
		$EAN = $A[1];
		$output = Item::get($EAN);
		
		return $output == null ? 4 : 0;
	}
	
	
	
	print( "This is not a product command I recognize." );
	return 2;
}


