<?php

class game
{
	var $data;
	var $x;
	var $y;
	var $hp = 25;
	var $dmg = "1d4";
	var $level = 1;
	var $xp = 0;
	
	function __construct()
	{
		$this->init();
	}
	
	function init()
	{
		// Basic stuff
		$this->x = 0;
		$this->y = 0;
		
		// Map (load this from a file instead)
		$map[0][0]['description'] = "<b>[Dusty Shack]</b><br>You're in an old dusty shack.<br>You see a door to the south.";
		$map[0][1]['description'] = "<b>[Choked Lawn]</b><br>You're standing in a lawn choked with dead weeds.<br>A shack is to the north.<br>The rest of the area is fenced in, aside from a small gap in the fence to the East which you might be able to pass through.";
		$map[1][1]['description'] = "<b>[Untamed Grasslands]</b><br>You've come upon a patch of untamed grass, likely a protected patch of land for wildlife.  Since the great calamity, however, it has gone largely unkempt.  Walking through the tall weeds, you periodically expeirence a strong sense of 'being watched', as if something were lurking in the tall brush.  Looking around you can see a small, fenced in area to the West, a path continuing to the East and a decrepit shack some ways to the North-West.";
		$map[2][1]['description'] = "<b>[Suth's Fortress Entrance]</b><br>You are standing in front of large fortress. A path runs to the West and a drawbridge leads inside the fortress to the East.";
		$map[3][1]['description'] = "<b>[Suth's Fortress]</b><br>You are in a dark stone fortress. A drawbridge is seen to the West.";
		
		$this->data = $map;
		
		// Creatures
		$this->add_creature($this->creatures("zombie"), 0, 0);
		$this->add_creature($this->creatures("zombie"), 0, 1);
		$this->add_creature($this->creatures("zombified_dog"), 1, 1);
		$this->add_creature($this->creatures("zombie"), 2, 1);
		$this->add_creature($this->creatures("zombie"), 2, 1);
		$this->add_creature($this->creatures("suth"), 3, 1);
	}
	
	function creatures( $name )
	{
		$creatures = array(
							"zombie" => array(
												"description" => "<span style='color:red;'>A Rotting Zombie</span>", 
												"hp" => 10, 
												"dmg" => "1d2",
												"xp" => 10,
												"atk_msg" => "claws at you",
											  ),
							"zombified_dog" => array(
												"description" => "<span style='color:red;'>A Partially-Decomposed Canine</span>", 
												"hp" => 12, 
												"dmg" => "1d2",
												"xp" => 15,
												"atk_msg" => "bites you",
												),
							"suth" => array(
												"description" => "<span style='color:red;'>Suthezcian, the Dahk Magii</span>", 
												"hp" => 28, 
												"dmg" => "2d5",
												"xp" => 75,
												"atk_msg" => "hurls a dark orb of energy",
												),
						  );
						  
		return isset($creatures[$name]) ? $creatures[$name] : false;
	}
	
	function get_description()
	{
		$output = $this->data[$this->x][$this->y]['description'];
		
		if( !empty($this->data[$this->x][$this->y]['enemies']) )
		{
			$output .= "<br>There is:";
			
			foreach( $this->data[$this->x][$this->y]['enemies'] as $enemy )
			{
				$output .= "<br>" . $enemy['description'];
				
				if( $enemy['hp'] <= 0 )
				{
					$output .= " - DEAD";
				}
			}
		}
		
		return $output;
	}
	
	function attack()
	{
		$attacked = false; // have we attacked anything?
		$output = "";
		
		if( !empty($this->data[$this->x][$this->y]['enemies']) )
		{
			foreach( $this->data[$this->x][$this->y]['enemies'] as &$enemy )
			{
				if( $enemy['hp'] > 0 )
				{
					$attacked = true;
					
					// attack creature
					$dmg = $this->get_damage($this->dmg) + (2 * $this->level);
					$enemy['hp'] -= $dmg;
					$enemy['hp'] = ($enemy['hp'] < 0) ? 0 : $enemy['hp'];
					$output .= "<br>You do $dmg damage to {$enemy['description']}. He has {$enemy['hp']} hp.";
					
					if( $enemy['hp'] > 0 )
					{
						// creature attack you
						$dmg = $this->get_damage($enemy['dmg']);
						$this->hp -= $dmg;
						$this->hp = ($this->hp < 0) ? 0 : $this->hp;
						$output .= "<br>" . $enemy['description'] . " {$enemy['atk_msg']} for $dmg damage. You have {$this->hp} hp.";
						
						if( $this->hp <= 0 )
						{
							$output .= "<br>You are dead.";
							return $output;
						}
					}
					else
					{
						$output .= "<br>You killed " . $enemy['description'] . " and gained {$enemy['xp']} XP.";
						$this->xp += $enemy['xp'];
						
						if( $this->xp >= $this->level * 50 )
						{
							$this->xp = 0;
							$this->level++;
							$this->hp = 25 + ($this->level * 2) + $this->level;
							$output .= "<br><span style='color:green;'>You gained a level. You are now level {$this->level}. Your health has been restored. ({$this->hp} HP)</span>";
						}
					}	
				}
			}
			
			if( $attacked )
				return $output;
		}
		
		return "There's nothing to attack!";
	}
	
	function get_damage( $roll )
	{
		srand();
		list($count, $sides) = explode('d', $roll);
		
		$result = 0;
		for ($i = 0; $i < $count; $i++) 
		{
			$result += rand(0, $sides);
		}
		
		return $result;
	}
	
	function add_creature( $creature, $x, $y )
	{
		$this->data[$x][$y]['enemies'][] = $creature;
	}
	
	function move( $x, $y )
	{
		if( !empty($this->data[$this->x][$this->y]['enemies']) )
		{
			foreach( $this->data[$this->x][$this->y]['enemies'] as &$enemy )
			{
				if( $enemy['hp'] > 0 )
				{
					return "You can't leave. Creatures block your exit.";
				}
			}
		}
		
		if( isset($this->data[$x][$y]) )
		{
			$this->x = $x;
			$this->y = $y;
			
			return $this->get_description();
		}
		
		return "Can't go that way";
	}
}

?>