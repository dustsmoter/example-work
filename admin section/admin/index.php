<?php

require_once("../common.inc");

$page = "home";

// Verify admin is logged in
if( !$user->logged_in() )
{
    header("Location: login.php");
    exit();
}

?>

<? include("header.php"); ?>



<? include("footer.php"); ?>