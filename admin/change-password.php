<?php

include( "assets/header.php" );

$msg = null;

if ( !empty($_POST["old-pass"]) && !empty($_POST["new-pass"]) && !empty($_POST["new-pass2"]) )
{
	if ( ! Password::authenticate( AUTH_USER, $_POST["old-pass"] ) )
	{
		$msg = "Current password invalid.";
	}
	elseif ( $_POST["new-pass"] != $_POST["new-pass2"] )
	{
		$msg = "New passwords did not match.";
	}
	elseif ( strlen($_POST["new-pass"]) < 5 )
	{
		$msg = "New password not long enough.";
	}
	else
	{
		Password::set_password( AUTH_USER, $_POST["new-pass"] );
		$msg = "Password updated successfully.";
	}
}

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

	<form class="form-signin" action="change-password.php" method="POST">
		<h2 class="form-signin-heading">Change password</h2>
		<?php if ( strlen($msg) ) print( "<p><strong>" . htmlspecialchars($msg) . "</strong></p>\n" ) ?>
		<p>User <strong><?=AUTH_USER?></strong></p>
		<input type="password" name="old-pass"  class="input-block-level" placeholder="Current password" />
		<input type="password" name="new-pass"  class="input-block-level" placeholder="New password" />
		<input type="password" name="new-pass2" class="input-block-level" placeholder="Repeat new password" />
		<button class="btn btn-large btn-primary" type="submit">Submit</button>
	</form>

</div>
<?php



include( "assets/footer.php" );
