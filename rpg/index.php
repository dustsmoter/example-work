<?php

require("classes/class.game.php");

session_start();

$game = new game();
$s = serialize($game);
file_put_contents('session/game_' . session_id(), $s);

?>

<html>
<head>
<title>Rpg</title>

<script src="js/jquery.js"></script>
<script>

$(document).ready(function()
{
	$('#attack').click(function(){command("attack");});
	$('#status').click(function(){command("status");});
	$('#north').click(function(){command("north");});
	$('#south').click(function(){command("south");});
	$('#west').click(function(){command("west");});
	$('#east').click(function(){command("east");});
	$('#look').click(function(){command("look");});
	$('#pickup').click(function(){command("pickup");});
});

function command( cmd )
{
	$.ajax(
	{
		type: "POST",
		url: "ajax.php",
		data: "cmd=" + cmd,
		success: function(msg)
		{
			//$('#command').val("");
			parseCommand(msg);
			$('#screen').scrollTop($('#screen').attr("scrollHeight"));
		}
	 });
}

function parseCommand(msg)
{
	$("#screen").append(msg);
}

</script>

<style type="text/css">

#screen
{
	width: 1024px;
	color: #ffffff;
	background-color: #000000;
	border: 1px solid green;
}

#screen
{
	height: 500px;
	overflow: scroll;
}

</style>

</head>

<body>

<div id="screen"><?=$game->get_description()?><br></div>
<!--<input type="text" name="comamand" id="command">-->
<input type="button" id="look" value="Look"> 
<input type="button" id="attack" value="Attack"> 
<input type="button" id="status" value="Status"> 
<input type="button" id="pickup" value="Pickup"> 
<table>
	<tr>
		<td colspan="2" align="center">
			<input type="button" id="north" value="North"> 
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" id="west" value="West"> 
		</td>
		
		<td>
			<input type="button" id="east" value="East"> 
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="button" id="south" value="South"> 
		</td>
	</tr>
</table>

</body>

</html>