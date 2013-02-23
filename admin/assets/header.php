<?php

header( "Content-type: text/html; charset=UTF-8" );

$link = function( $href )
{
	global $api_root;
	
	list($script_name) = explode( "?", $_SERVER["REQUEST_URI"] );
	$href = $api_root . "admin/" . $href;
	
	$rv = "<a href=\"" . htmlspecialchars($href) . "\">";
	
	list($scr) = explode( "?", $href );
	if ( $scr == $script_name )
		return "<li class=\"active\">" . $rv;
	
	return "<li>" . $rv;
};

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		
		<link rel="stylesheet" href="assets/css/dibs-admin.css"/>
		
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">

		<script src="assets/js/jquery-1.9.1.js"></script>
		
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
					</button>
					<a class="brand" href="<?=$api_root?>admin/">Dibs admin</a>
					<div class="nav-collapse collapse">
						<ul class="nav">
							<?=$link( "orders.php" )?>Orders</a></li>
						</ul>
						<div style="float: right; padding: 6px 0px 6px 5px;">
							<a title="Logout" href="<?=$api_root?>admin/logout.php"><i style="color: #777;" class="icon-signout icon-2x"></i></a>
						</div>
						<div style="float: right; padding: 6px 5px;">
							<a title="Change password" href="<?=$api_root?>admin/change-password.php"><i style="color: #777;" class="icon-cog icon-2x"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="canvas">
			
<?php

unset($link);
