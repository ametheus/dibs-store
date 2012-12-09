
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
	Javascript implementation of a dibs store
*/


var CallDibs = (function()
{
	var G = {
		api: function() { console.warn( "API has not yet been initialized!" ); }
	};
	
	var call_wrapper = function( callback )
	{
		// TODO: handle failures
		return callback;
	};
	
	var cd = function( api_url )
	{
		var api = function( uri, callback, data, method )
		{
			if ( !method ) method = data ? "POST" : "GET";
			
			var ajax = {
				url: api_url + uri,
				cache: false,
				dataType: "json",
				success: call_wrapper( callback )
			};
			
			$.ajax(ajax);
		};
		G.api = api;
	};
	
	cd.___TODO__remove__call_api_directly = function(a,b,c,d) { G.api(a,b,c,d); };
	
	return cd;
})();
