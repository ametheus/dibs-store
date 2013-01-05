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
	HTML framework (pun intended) for embedding a dibs store inside an iframe.
*/


require_once( "../lib/settings.defaults.php" );
@include_once( "../settings.php" );


/**
 * Build an URI from a relative link
 **/
function uri( $link )
{
	global $api_use_https, $api_host, $api_root;
	
	if ( substr($api_root,-1) != "/" ) $api_root .= "/";
	$https = $api_use_https ? "https" : "http";
	
	return "{$https}://{$api_host}{$api_root}{$link}";
}



?><!DOCTYPE html>
<html>
	<head>
		<title>Dibs store</title>
		
		<link rel="stylesheet" href="<?=uri('front/css/dibs.css')?>" />
		
		<script src="<?=uri('front/js/jquery.js')?>"></script>
		<script src="<?=uri('front/js/jquery.cookie.js')?>"></script>
		
		<script src="<?=uri('front/js/dibs.js')?>"></script>
		<script>
			$(function()
			{
				CallDibs("<?=uri('')?>");
			});
		</script>
	</head>
	<body>
	</body>
</html>