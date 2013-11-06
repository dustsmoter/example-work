<?php

require("classes/class.game.php");

session_start();

$s = file_get_contents('session/game_' . session_id());
$game = unserialize($s);

if( !is_object($game) )
{
	die("--- No session started ---");
}

if( !empty($_POST) )
{
	$cmd = $_POST['cmd'];
	
	$output = "<br><b>$cmd</b><br>";
	
	if( $game->hp <= 0 )
	{
		die("You are dead.<br>");
	}
	
	switch( $cmd )
	{
		case 'n':
			$output .= $game->move($game->x, $game->y - 1);
		break;
		
		case 's':
			$output .= $game->move($game->x, $game->y + 1);
		break;
		
		case 'w':
			$output .= $game->move($game->x - 1, $game->y);
		break;
		
		case 'e':
			$output .= $game->move($game->x + 1, $game->y);
		break;
		
		case 'attack':
			$output .= $game->attack();
		break;
		
		case 'look':
			$output .= $game->get_description();
		break;
	}
	
	$output .= "<br>";
	
	// save game data
	$s = serialize($game);
	file_put_contents('session/game_' . session_id(), $s);
	
	// return output
	echo $output;
}

?>