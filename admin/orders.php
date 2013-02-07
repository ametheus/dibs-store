<?php

require_once( "assets/header.php" );

?>

<style>

	#order-container
	{
		width: 100%;
		min-height: 800px;
		background: url(./assets/stripe.png) repeat;
	}

	td, th
	{
		vertical-align: top;
		
	}
	tr
	{
		background-color: #ffffff;
		padding: 5px;
	}
	tr:nth-child(even)
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
					
					// Invoice number
					rv += '<td>' + cart["invoice-no"] + '</td>';
					
					// Permalink in shop frontend
					rv += '<td><a href="' + shop_root + "#cart-" + cart_id + '">permalink</a></td>';
					
					// Invoice PDF
					rv += '<td><a href="download-invoice.php?cart-id=' + cart_id + '">invoice</a></td>';
					
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
						total += ( ( cart.items[j].count ? cart.items[j].count : 1) * cart.items[j].price.amount );
					rv += '<td class="right"><strong>' + total.toFixed(2) + '</strong></td>';
					
					// Status fields
					rv += '<td>' + (cart.status.confirmed ? 1 : 0) + '</td>';
					rv += '<td>' + (cart.status.paid ? 1 : 0) + '</td>';
					rv += '<td>' + (cart.status.sent ? 1 : 0) + '</td>';
					
					rv += '</tr>';
					
					
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
