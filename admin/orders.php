<html>
	<head>
		<link rel="stylesheet" href="assets/dibs-admin.css"/>

		<script src="assets/jquery-1.9.1.js"></script>
		<!--<script src="assets/jquery.hashchange.js"></script>-->

	</head>
	<body>
		<div class="canvas">
			<div class="header">
				<div class="links">
<!-- 					<div><a href="#get_nieuw_klantenservice">Nieuw</a>&nbsp;(<span class="count get_nieuw_klantenservice">-</span>)</div>
					<div><a href="#get_automatisch_klantenservice">Automatisch</a>&nbsp;(<span class="count get_automatisch_klantenservice">-</span>)</div>
					<div><a href="#get_handmatig_klantenservice">Handmatig</a>&nbsp;(<span class="count get_handmatig_klantenservice">-</span>)</div>
					<div><a href="#get_geparkeerd">Geparkeerd</a>&nbsp;(<span class="count get_geparkeerd">-</span>)</div>
					<div><a href="#get_leverbare_backorders">Backorders</a>&nbsp;(<span class="count get_leverbare_backorders">-</span>)</div>
					<div><a href="#get_1600">Na 16:00</a>&nbsp;(<span class="count get_1600">-</span>)</div>
					<div><a href="#get_alle_backorders">Alle</a>&nbsp;</div>
					<div><a href="#get_magazijn">Magazijn</a>&nbsp;(<span class="count get_magazijn">-</span>)</div>
					<div><a href="#get_postnl">PostNL</a>&nbsp;(<span class="count get_postnl">-</span>)</div>
					<div><a href="#get_rest">Rest</a>&nbsp;(<span class="count get_rest">-</span>)</div>
					<div><a href="#get_klanten">Klanten</a>&nbsp;(<span class="count get_klanten">-</span>)</div>
					<div><img src="http://static.managementboek.nl/jira-icons/refresh.gif" class="reload"/></div>
 -->				</div>
			</div>
			
			<div id="order-container">
				<table>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</body>
	<script>

$(function()
{
	var presets = {
		"Onverwerkt": { archive: 0, confirmed: 1, paid: 1, sent: 0 },
		"Gestrand":   { archive: 0, confirmed: 1, paid: 0 },
		"Verwerkt":   { archive: 0, confirmed: 1, paid: 1, sent: 0 }
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
			
			return '<td>' + I.count + 'x</td>' +
				'<td>' + I.title + '</td>';
		}
		
		$.ajax({
			url: "./ajax/orders-by-status.php",
			data: presets[preset],
			dataType: 'json',
			success: function( data )
			{
				if ( location.hash.replace("#","") != preset ) return;
				
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
					rv += '<td'+rsp+'><strong>' + total + '</strong></td>';
					
					// Status fields
					rv += '<td'+rsp+'>' + cart.status.confirmed ? 1 : 0 + '</td>';
					rv += '<td'+rsp+'>' + cart.status.paid ? 1 : 0 + '</td>';
					rv += '<td'+rsp+'>' + cart.status.sent ? 1 : 0 + '</td>';
					
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
		change_preset( "Onverwerkt", true );
});

	</script>
</html>
