<?php

require_once( "assets/header.php" );

?>

<style>

	#order-container
	{
		width: 100%;
		min-height: 800px;
		background: url(stripe.png) repeat;
	}

	tr
	{
		background-color: #ffffff;
	}
	tr:even
	{
		background-color: #dadada;
	}

</style>

<div id="order-container">
	<table>
		<tbody></tbody>
	</table>
</div>

<script>

$(function()
{
	var presets = {
		"Open":       { archive: 0, confirmed: 1, paid: 1, sent: 0 },
		"Stranded":   { archive: 0, confirmed: 1, paid: 0 },
		"Processed":  { archive: 0, confirmed: 1, paid: 1, sent: 1 }
	};
	
	for ( i in presets )
	{
		$(".header .links").append('<div><a data-preset="'+i+'" href="#'+i+'">'+i+'</a>&nbsp;</div>');
	}
	
	
	var change_preset = function( preset, force )
	{
		if ( !( preset in presets )) return console.log(preset);
		$(".header .links > div").removeClass( "active" );
		
		if ( !force && location.hash.replace("#","") == preset ) return console.log("exit");
		
		location.hash = preset;
		
		// TODO:
		var shop_root = "https://database.collegiummusicum.nl/ticketshop/";
		
		var tb = $("#order-container table tbody")
		
		var fmt_item = function( I )
		{
			if ( !I ) return "<td></td><td></td>";
			
			var ct = I.count ? I.count + "x" : "";
			if ( I.EAN == "PORTO" ) ct = "";
			
			return '<td class="right">' + ct + '</td>' +
				'<td>' + I.title + '</td>';
		}
		
		$.ajax({
			url: "./ajax/orders-by-status.php",
			data: presets[preset],
			dataType: 'json',
			success: function( data )
			{
				tb.empty()
				for ( var i = 0; i < data.length; i++ ) (function(i)
				{
					var cart = data[i];
					var rv = "<tr>";
					
					var cart_id = cart["cart-id"];
					
					var rsp = cart.items.length > 0 ? ' rowspan="'+cart.items.length+'"' : '';
					
					// Invoice number
					rv += '<td'+rsp+'>' + cart["invoice-no"] + '</td>';
					
					// Permalink in shop frontend
					rv += '<td'+rsp+'><a href="' + shop_root + "#cart-" + cart_id + '">permalink</a></td>';
					
					// Invoice PDF
					rv += '<td'+rsp+'><a href="download-invoice.php?cart-id=' + cart_id + '">invoice</a></td>';
					
					// Item #1
					rv += fmt_item( cart.items[0] );
					
					// Total amount
					var total = 0;
					for ( var j = 0; j < cart.items.length; j++ )
						total += ( ( cart.items[j].count ? cart.items[j].count : 1) * cart.items[j].price.amount );
					rv += '<td class="right"'+rsp+'><strong>' + total.toFixed(2) + '</strong></td>';
					
					// Status fields
					rv += '<td'+rsp+'>' + (cart.status.confirmed ? 1 : 0) + '</td>';
					rv += '<td'+rsp+'>' + (cart.status.paid ? 1 : 0) + '</td>';
					rv += '<td'+rsp+'>' + (cart.status.sent ? 1 : 0) + '</td>';
					
					rv += '</tr>';
					
					for ( var j = 1; j < cart.items.length; j++ )
					{
						rv += '<tr>' + fmt_item( cart.items[j] ) + '</tr>';
					}
					
					tb.append(rv);
					
				})( i );
			}
		});
	};
	
	
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
