
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
	
	
	G.fmt_item_long = function( target, item )
	{
		var ean = item.EAN;
		
		$(target).addClass("dibs-product").addClass("dibs-product-short").attr("data-ean",ean);
		$(target).append($("<h3/>").html(item.title));
		$(target).append($("<div/>").addClass("dibs-order").html(G.fmt_order_button(ean)));
		$(target).append($("<p/>").addClass("dibs-description").html(item.description));
		$(target).append($("<div/>").addClass("dibs-price").html(G.fmt_price(item.price)));
	};
	
	G.fmt_order_button = function( ean )
	{
		var count = 0;
		if ( G.active_cart )
		{
			for ( var i = 0; i < G.active_cart.items.length; i++ )
			{
				var it = G.active_cart.items[i];
				if ( it.EAN == ean )
					count += it.count;
			}
		}
		
		if ( count == 0 )
			return '<button class="dibs-order-button"><span>Bestellen</span></button>';
		
		return '<label>Aantal: ' +
				'<input type="text" class="dibs-count" value="' + count + '" />' +
			'</label>' +
			'<button class="dibs-plus"><span>Meer</span></button>' +
			'<button class="dibs-minus"><span>Minder</span></button>' +
			'<button class="dibs-cancel"><span>Verwijderen</span></button>';
	};
	
	G.fmt_price = function( price )
	{
		if ( price.original_price )
		{
			var simplified = {
				currency: price.currency,
				amount: price.amount
			};
			
			return '<del class="dibs-original-price">' + 
				G.fmt_price( price.original_price ) + "</del>" +
				'<span class="dibs-current-price">' +
				G.fmt_price( simplified ) + "</del>";
		}
		
		var friendly_currencies = {
			"EUR": "&#x20AC;",
			"BTC": "&#x0243;"
		};
		
		var cur = price.currency;
		if ( cur in friendly_currencies ) cur = friendly_currencies[cur];
		
		// TODO: nice formatting
		var amt = price.amount;
		
		return '<span class="dibs-currency">' + cur + '</span>' +
			'<span class="dibs-amount">' + amt + '</span>';
	};
	
	
	var call_wrapper = function( callback )
	{
		var ign = callback.pass_error ? callback.pass_error : [];
		
		return function( data )
		{
			if ( data.status == 0 || $.inArray( data.status, ign ) >= 0 )
			{
				callback( data.output, data.status );
				return;
			}
			
			alert( "Error " + data.status + ": " + data.error + "\n\n" +
				"See " + data.see_also + " for more information." );
		};
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
				success: call_wrapper( callback ),
				error: function( jqXHR, textStatus, errorThrown )
				{
					if ( textStatus == "error" )
					{
						// HTTP error; the dibs api throws these sometimes.
						call_wrapper( callback )( $.parseJSON( jqXHR.responseText ) );
						return;
					}
					console.log([ jqXHR, textStatus, errorThrown ])
				}
			};
			
			$.ajax(ajax);
		};
		G.api = api;
		
		// HACK
		G.dibs_root = $("body");
		
		api("1/category/all", function(data)
		{
			if ( !data.items.length ) return;
			
			for ( var i = 0; i < data.items.length; i++ )
			{
				var it = $("<div/>");
				G.fmt_item_long( it, data.items[i] );
				G.dibs_root.append( it );
			}
		});
	};
	
	
	cd.___TODO__remove__call_api_directly = function(a,b,c,d) { G.api(a,b,c,d); };
	
	return cd;
})();
