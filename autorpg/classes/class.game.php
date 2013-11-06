<?php

class game 
{
	protected $player;
	protected $quest;
	protected $narrate;
	
	protected $event_history;	// History of events for the player
	
	function __construct( &$player, &$quest, &$narrate )
	{
		if( is_a($player, "player") )
		{
			$this->player = $player;
		}
		
		if( is_a($quest, "quest") )
		{
			$this->quest = $quest;
		}
		
		if( is_a($narrate, "narrate") )
		{
			$this->narrate = $narrate;
		}
		
		$this->game_loop();
	}
	
	function game_loop()
	{
		
	}
}

?>