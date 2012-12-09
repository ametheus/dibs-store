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
	Methods for manipulating store items
*/


class Item
{
	/**
	 * Check if the item exists in the database.
	 **/
	static function exists( $EAN )
	{
		$db = db();
		$it = $db->items->find(array( "EAN" => $EAN ));
		return ( $it->count() >= 1 );
	}
	
	
	/**
	 * Get one item description.
	 **/
	static function get( $EAN, $short = 0 )
	{
		$db = db();
		$rv = $db->items->findOne(array( "EAN" => $EAN ));
		
		// TODO: shorten item object if necessary
		// TODO: convert currencies
		
		return $rv;
	}
	
	
	/**
	 * Get all items in category {category_name}.
	 * 
	 * Optional: Shortness {short}, or sort by {sort}.
	 **/
	static function get_category( $category_name, $short = 0, $sort = array() )
	{
		$category_name = (string)$category_name;
		
		$rv = db()->categories->findOne(array("cat-id" => $category_name));
		if ( ! $rv ) $rv = array( "name" => $category_name );
		
		// TODO: add sub-/supercategories.
		
		if ( $category_name == "all" )
			$items = db()->items->find();
		else
			$items = db()->items->find(array("categories" => $category_name));
		
		$rv["items"] = array();
		
		// TODO: implement paging.
		
		while ( $items->hasNext() )
		{
			$it = $items->getNext();
			
			// TODO: implement sorting
			
			// TODO: shorten item object if necessary
			// TODO: convert currencies
			
			$rv["items"][] = $it;
		}
		
		return $rv;
	}
}

