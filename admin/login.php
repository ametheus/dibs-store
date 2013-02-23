<?php

$ct = "/";
if ( !empty($_REQUEST["continue"]) )  $ct = $_REQUEST["continue"];

if ( AUTH_USER )
{
	// User is logged in; redirect to wherever they wanted to go.
	header( "HTTP/1.1 302 See Other" );
	$ct = str_replace( array("\n","\r"), "", $ct );
	header( "Location: {$ct}" );
}

$msg = "Please sign in";

if ( !empty($_POST["username"]) )  $msg = "Login incorrect. Please try again.";

include( "assets/header.php" );

?></div>
<style type="text/css">
	.form-signin
	{
		max-width: 300px;
		padding: 19px 29px 29px;
		margin: 90px auto 20px;
		background-color: #fff;
		border: 1px solid #e5e5e5;
		-webkit-border-radius: 5px;
		   -moz-border-radius: 5px;
		        border-radius: 5px;
		-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
		   -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
		        box-shadow: 0 1px 2px rgba(0,0,0,.05);
	}
	.form-signin .form-signin-heading,
	.form-signin .checkbox
	{
		margin-bottom: 10px;
	}
	.form-signin input[type="text"],
	.form-signin input[type="password"]
	{
		font-size: 16px;
		height: auto;
		margin-bottom: 15px;
		padding: 7px 9px;
	}
</style>
<div class="container">

	<form class="form-signin" action="login.php" method="POST">
		<h2 class="form-signin-heading"><?=$msg?></h2>
		<input type="text" name="username" class="input-block-level" placeholder="Username">
		<input type="password" name="password" class="input-block-level" placeholder="Password">
		<!-- <label class="checkbox">
			<input type="checkbox" value="remember-me"> Remember me
		</label> -->
		<input type="hidden" name="continue" value="<?=htmlspecialchars($ct)?>" />
		<button class="btn btn-large btn-primary" type="submit">Sign in</button>
	</form>

</div>
<?php



include( "assets/footer.php" );
