<?php

function my_autoloader($class) {
    include 'classes/' . $class . '.class.php';
}

spl_autoload_register('my_autoloader');

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
	
	$apply_status_commands = array('north','south','west','east','attack');
	if (in_array($cmd, $apply_status_commands))
		$output .= $game->statuses(); // Handle poisons, other effects, etc when moving or attacking.
	
	if( $game->hp <= 0 )
	{
		die("You are dead.<br>");
	}
	
	switch( $cmd )
	{
		case 'north':
			$output .= $game->move($game->x, $game->y - 1);
		break;
		
		case 'south':
			$output .= $game->move($game->x, $game->y + 1);
		break;
		
		case 'west':
			$output .= $game->move($game->x - 1, $game->y);
		break;
		
		case 'east':
			$output .= $game->move($game->x + 1, $game->y);
		break;
		
		case 'attack':
			$output .= $game->attack();
		break;
		
		case 'look':
			$output .= $game->get_description();
		break;
		
		case 'status':
			$output .= $game->get_statuses();
		break;
		
		case 'pickup':
			$output .= $game->pickup_items();
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