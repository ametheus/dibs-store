<?php

require_once( "assets/header.php" );

?>

<style>

	#order-container
	{
		width: 100%;
		padding: 1px 0px;
		min-height: 800px;
		background: url(./assets/img/stripe.png) repeat;
	}
	#order-container table
	{
		margin: 15px 20px;
	}

	td, th
	{
		text-align: left;
	}
	tbody tr
	{
		background-color: #ffffff;
		padding: 5px;
	}
	tbody tr:nth-child(even)
	{
		background-color: #e8e8e8;
	}
	

</style>

<div class="header">
	<div class="links"></div>
</div>

<div id="order-container">
	<table>
		<thead>
			<tr>
				<th>Inv. no.</th>
				<th>Link</th>
				<th>Name</th>
				<th colspan="2">Items</th>
				<th>Amount</th>
				<th>Order status</th>
			</tr>
		</thead>
		<tbody></tbody>
		<tfoot>
			<tr>
				<th colspan="3">Total</th>
				<td class="total right"></td>
				<td class="total"></td>
				<td class="total right"></td>
				<th class="total right"></th>
			</tr>
		</tfoot>
	</table>
</div>

<script>

$(function()
{
	var presets = {
		"Stranded":   { cancelled: 0, archive: 0, confirmed: 1, paid: 0 },
		"Open":       { cancelled: 0, archive: 0, confirmed: 1, paid: 1, sent: 0 },
		"Processed":  { cancelled: 0, archive: 0, confirmed: 1, paid: 1, sent: 1 }
	};
	
	for ( i in presets )
	{
		$(".header .links").append('<div><a data-preset="'+i+'" href="#'+i+'">'+i+'</a>&nbsp;</div>');
	}
	
	var status_icon = function( status, value, icon )
	{
		var alttext = status;
		if ( value )
		{
			for ( var i = 0; i < value.length; i++ )
				alttext += "\nSet by [" + value[i].user + "] on [" + 
					(new Date(value[i].date.sec*1000)).toLocaleString() + "]";
			if ( !value.length ) value = false;
		}
		else
			alttext = "Click to mark order as '"+status+"'";
		
		return '<div class="status-icon ' + status +
			( value ? ' active' : '' ) + '"' +
			' title="' + alttext + '"' +
			' data-status="' + status + '"' +
			'>' + 
			'<i class="' + icon + '"></i>' +
			'</div>';
	}
	
	
	var tb = $("#order-container table tbody");
	var total_foot = $("#order-container table tfoot .total");
	
	var change_preset = function( preset, force )
	{
		if ( !( preset in presets )) return console.log(preset);
		
		$(".header .links > div").removeClass( "active" );
		$(".header .links > div a[data-preset='" + preset + "']").parent().addClass( "active" );
		
		if ( !force && location.hash.replace("#","") == preset ) return console.log("exit");
		
		location.hash = preset;
		
		tb.html('<tr style="background: none;"><td style="vertical-align: middle;" colspan="7">' +
			'<i class="icon-spinner icon-spin icon-3x"></i> One moment please...</td></tr>');
		total_foot.empty();
		
		// TODO:
		var shop_root = "https://database.collegiummusicum.nl/ticketshop/";
		
		$.ajax({
			url: "./ajax/orders-by-status.php",
			data: presets[preset],
			dataType: 'json',
			success: function( data )
			{
				var rows = { html: "" };
				var totals_by_ean = {};
				
				for ( var i = 0; i < data.length; i++ ) (function(i)
				{
					var cart = data[i];
					var cart_id = cart["cart-id"];
					
					var rv = '<tr data-cart="' + cart_id + '">';
					
					// Invoice number
					rv += '<td>' + cart["invoice-no"] + '</td>';
					
					
					// Links
					rv += '<td>';
					
					// Permalink in shop frontend
					var pm = shop_root + "#cart-" + cart_id;
					rv += '<a style="margin-right: 15px;" title="Permalink to this order" href="' + pm + '"><i class="icon-link"></i></a>';
					
					// Invoice PDF
					rv += '<a href="download-invoice.php?cart-id=' + cart_id + '"><i class="icon-download"></i> Invoice</a>';
					
					rv += '</td>';
					
					
					// The name on the billing address
					rv += '<td>' + cart.address.billing.name + '</td>';
					
					// Cart items
					var counts = '', titles = '';
					var porto = 0;
					for ( var j = 0; j < cart.items.length; j++ )
					{
						var I = cart.items[j];
						if ( I.EAN == "PORTO" )
						{
							porto++;
							continue;
						}
						
						if ( j != porto )
						{
							counts += "<br />";
							titles += "<br />";
						}
						
						if ( I.count && ( I.EAN != "PORTO" ) )
							counts += I.count + "x";
						
						titles += I.title;
					}
					if ( porto && j != porto )
					{
						counts += "<br />";
						titles += "<br />";
					}
					if ( porto )
						titles += "Verzendkosten";
					
					rv += '<td>'+counts+'</td>';
					rv += '<td>'+titles+'</td>';
					
					// Total amount
					var total = 0;
					for ( var j = 0; j < cart.items.length; j++ )
					{
						var I = cart.items[j];
						var ol = ( I.count ? I.count : 1 ) * (I.price ? I.price.amount : 0);
						total += ol;
						
						if ( !( I.EAN in totals_by_ean ))
							totals_by_ean[ I.EAN ] = { title: I.title, count: 0, amount: 0 };
						
						totals_by_ean[ I.EAN ].amount += ol;
						totals_by_ean[ I.EAN ].count += ( I.count ? I.count : 1 );
					}
					rv += '<td class="right"><strong>' + total.toFixed(2) + '</strong></td>';
					
					// Status fields
					rv += '<td>';
					rv += status_icon( 'confirmed', cart.status.confirmed, 'icon-shopping-cart' );
					rv += status_icon( 'paid',      cart.status.paid,      'icon-money' );
					rv += status_icon( 'sent',      cart.status.sent,      'icon-truck' );
					rv += '&nbsp;&nbsp;';
					rv += status_icon( 'cancelled', cart.status.cancelled, 'icon-remove-circle' );
					rv += '</td>';
					
					rv += '</tr>';
					
					
					rows.html += rv;
					
				})( i );
				
				tb.html( rows.html );
				
				var i = {i:0,t:0};
				var add_total = function( X )
				{
					if ( i.i )
						total_foot.append( "<br />" );
					
					i.t += X.amount;
					
					$(total_foot[0]).append( X.count + "x" );
					$(total_foot[1]).append( X.title );
					$(total_foot[2]).append( "€&nbsp;" + X.amount.toFixed(2) );
					$(total_foot[3]).html( "€&nbsp;" + i.t.toFixed(2) );
					
					
					i.i++;
				}
				
				for ( ean in totals_by_ean )
					if ( ean != "PORTO" )
						add_total( totals_by_ean[ean] );
				if ( totals_by_ean.PORTO )
					add_total( totals_by_ean.PORTO );
			}
		});
	};
	
	
	tb.on( "click", "div.status-icon", function()
	{
		var icon = $(this);
		if ( icon.hasClass("active") ) return;
		
		var status = icon.attr("data-status");
		var cart_id = icon.parents("tr").attr("data-cart");
		
		if ( !confirm("Add status ["+status+"] to this order?") ) return;
		
		$.ajax({
			url: "ajax/order-set-status.php",
			type: "post",
			data: { status: status, "cart-id": cart_id },
			success: function()
			{
				icon.addClass("active");
				icon.attr("title","Switch tabs or reload the page to update order status");
			}
		});
		
		console.log([ cart_id, status ]);
	})
	
	
	$(".header .links a").click(function()
	{
		var preset = $(this).attr("data-preset");
		change_preset( preset );
	});
	
	var pr = location.hash.replace("#","");
	if ( pr in presets )
		change_preset( pr, true );
	else
		change_preset( "Open", true );
});

</script>

<?php

require_once( "assets/footer.php" );
