<?php

require_once("../common.inc");

// User is attempting to login
if( !empty($_POST['login']) )
{
	if( $user->login($_POST) )
	{
		$_SESSION['user'] = $user->user_data;
		session_write_close();
		header("Location: index.php");
		exit();
	}
	else
	{
		$message = "Username / Password incorrect";
	}
}

?>

<? $_menu_hide = true; include("header.php"); ?>

<? if( !empty($message) ): ?>
	<?=$message?><br /><br />
<? endif; ?>

<form method="post">
	Username: <input type="text" name="username" /><br />
	Password: <input type="password" name="password" /><br />
	<input type="submit" name="login" value="Login">
</form>

<? include("footer.php") ?>