<?php

class game
{
	var $data;
	var $x;
	var $y;
	var $hp = 25;
	var $weapon = array();
	var $level = 1;
	var $xp = 0;
	var $statuses = array();
	
	function __construct()
	{
		$this->init();
	}
	
	function init()
	{
		// Basic stuff
		$this->x = 0;
		$this->y = 0;
		
		// Map
		$map[0][0]['description'] = "<b>[Dusty Shack]</b><br>You're in an old dusty shack.<br>You see a door to the south.";
		$map[0][1]['description'] = "<b>[Choked Lawn]</b><br>You're standing in a lawn choked with dead weeds.<br>A shack is to the north.<br>The rest of the area is fenced in, aside from a small gap in the fence to the East which you might be able to pass through.";
		$map[1][1]['description'] = "<b>[Untamed Grasslands]</b><br>You've come upon a patch of untamed grass, likely a protected patch of land for wildlife.  Since the great calamity, however, it has gone largely unkempt.  Walking through the tall weeds, you periodically experience a strong sense of 'being watched', as if something were lurking in the tall brush.  Looking around you can see a small, fenced in area to the West, a path continuing to the East and a decrepit shack some ways to the North-West.";
		$map[2][1]['description'] = "<b>[Kluth's Fortress Entrance]</b><br>You are standing in front of large fortress. A path runs to the West and a drawbridge leads inside the fortress to the East.";
		$map[3][1]['description'] = "<b>[Kluth's Fortress]</b><br>You are in a dark stone fortress. A dark hallway meanders to the East. A drawbridge is seen to the West.";
		$map[4][1]['description'] = "<b>[Kluth's Fortress Hallway]</b><br>You are in a long, dark hallway that ends at a intersection - it continues both North and South The fortress entrance is to the West.";
		$map[4][0]['description'] = "<b>[Kluth's Fortress Hallway North]</b><br>The hallway stops at a giant oak door braced with corroded iron. It does not open. The hallway continues south.";
		$map[4][2]['description'] = "<b>[Kluth's Fortress Hallway South]</b><br>Your boots clang lightly among the rough-hewn stone. Further South a spiral staircase descends into darkness. The hall continues to north.";
		
		$this->data = $map;
		
		// Creatures
		// Initial zombie has a 100% chance to drop a dagger.
		$firstZombie = $this->creatures("zombie");
		$firstZombie['drops'] = array(
			'name' => 'rusty_dagger',
			'chance' => 100,
		);
		$this->add_creature($firstZombie, 0, 0);
		
		$this->add_creature($this->creatures("zombie"), 0, 1);
		$this->add_creature($this->creatures("zombified_dog"), 1, 1);
		$this->add_creature($this->creatures("zombie"), 2, 1);
		$this->add_creature($this->creatures("zombie"), 2, 1);
		$this->add_creature($this->creatures("kluth"), 3, 1);
		$this->add_creature($this->creatures("florin"), 4, 1);
		$this->add_creature($this->creatures("castle_archer"), 4, 2);
		$this->add_creature($this->creatures("castle_archer"), 4, 2);
		
		// Initial weapon
		$this->weapon = $this->weapons('fists');
	}
	
	function get_statuses()
	{
		$output = "<span style='color: red;'>HP:</span>: {$this->hp}<br>";
		$output .= "<span style='color: white;'>You are wielding:</span> {$this->weapon['name']} ({$this->weapon['dmg']}) - <i>{$this->weapon['description']}</i><br>";
		
		if (!empty($this->statuses)) {
			foreach ($this->statuses as $index => $status) {
				$output .= $status['description'] . " for " . $status['time_left'] . " more turns.<br>";
			}
		} else {
			$output .= "You have no status effects.<br>";
		}
		
		return $output;
	}
	
	function pickup_items() // TODO: only picks up one item (and assumes weapon)
	{
		$output = "";
		
		if( !empty($this->data[$this->x][$this->y]['items']) )
		{
			$item = $this->data[$this->x][$this->y]['items'][0];
			unset($this->data[$this->x][$this->y]['items'][0]);
			$oldWeapon = $this->weapon;
			$this->data[$this->x][$this->y]['items'][] = $oldWeapon;
			$this->weapon = $item;
			$output .= "You drop {$oldWeapon['name']} and pick up a " . $item['name'] . "<br>";
			
		} else {
			$output .= "There are no items on the ground.<br>";
		}
		
		return $output;
	}
	
	function statuses()
	{
		$output = "";
		
		if (!empty($this->statuses)) {
			foreach ($this->statuses as $index => $status) {
				$this->statuses[$index]['time_left']--;
				$dmg = $this->get_damage($status['dmg']);
				$this->hp -= $dmg;
				$this->hp = ($this->hp < 0) ? 0 : $this->hp;
				$output .= $status['description'] . " " . $status['message'] . " for $dmg damage. You have {$this->hp} hp.<br>";
				
				if ($this->statuses[$index]['time_left'] <= 0) {
					$output .= "<i>" . $status['end_message'] . "</i><br>";
					unset($this->statuses[$index]);
				}
			}
		}
		
		return $output;
	}
	
	function weapons ($name)
	{
		$weapons = array(
			'fists' => array(
				'dmg' => "1d2",
				'name' => "<span style='color: #c2c2d6;'>Fists</span>",
				'description' => "Strong fists calloused from years of hard labor.",
				'message' => array(
					'punch',
					'wallop',
				),
			),
			'rusty_dagger' => array(
				'dmg' => "1d3",
				'name' => "<span style='color: #c2c2d6;'>Rusty Dagger</span>",
				'description' => "A small dagger with flecks of spotting rust on the aged blade.",
				'message' => array(
					'stab',
					'pierce',
				),
			),
			'iron_dagger' => array(
				'dmg' => "1d4",
				'name' => "<span style='color: #c2c2d6;'>Iron Dagger</span>",
				'description' => "A small dagger forged in iron.",
				'message' => array(
					'stab',
					'pierce',
				),
			),
			'silver_dagger' => array(
				'dmg' => "1d6",
				'name' => "<span style='color: #c2c2d6;'>Silver Dagger</span>",
				'description' => "A small dagger brimming with silver.",
				'message' => array(
					'piercing stab',
				),
			),
		);
		
		return isset($weapons[$name]) ? $weapons[$name] : false;
	}
	
	function creatures( $name )
	{
	
		$statuses = array(
			'zombie_virus' => array(
				"name" => "zombie_virus",
				"chance" => 50,
				"duration" => 2,
				"stack" => false,
				"dmg" => "1d1",
				"init_message" => "<span style='color: #54C571;'>You feel something foreign around the bite mark.</span>",
				"end_message" => "<span style='color: white;'>The virus evaporates from your system.</span>",
				"message" => "<span style='color: #54C571;'>sizzles your blood</span>",
				"description" => "<span style='color: #54C571;'>Zombie virus</span>",
			),
			'lesser_poison' => array(
				"name" => "lesser_poison",
				"chance" => 75,
				"duration" => 3,
				"stack" => true,
				"dmg" => "1d3",
				"init_message" => "<span style='color: #728C00;'>A burning sensation at the wound.</span>",
				"end_message" => "<span style='color: white;'>The poison runs it's course.</span>",
				"message" => "<span style='color: #728C00;'>burns your blood</span>",
				"description" => "<span style='color: #728C00;'>Lesser poison</span>",
			),
			'mana_burn' => array(
				"name" => "mana_burn",
				"chance" => 75,
				"duration" => 4,
				"stack" => true,
				"dmg" => "2d1",
				"init_message" => "<span style='color: steelblue;'>Your soul feels like hot ash.</span>",
				"end_message" => "<span style='color: white;'>Your spirit overcomes the mana burn.</span>",
				"message" => "<span style='color: steelblue;'>singes your soul</span>",
				"description" => "<span style='color: steelblue;'>Mana burn</span>",
			),
		);
		
		$creatures = array(
			"castle_archer" => array(
				"description" => "<span style='color:red;'>Castle Archer</span>", 
				"hp" => 6, 
				"xp" => 15,
				"attacks" => array(
					array(
						"dmg" => "1d10",
						"message" => array(
							"shoots an arrow",
							"lets loose an arrow",
						),
					),
				),
			),
			"zombie" => array(
				"description" => "<span style='color:red;'>A Rotting Zombie</span>", 
				"hp" => 10, 
				"xp" => 10,
				"attacks" => array(
					array(
						"dmg" => "1d2",
						"message" => "claws at you",
					),
					array(
						"dmg" => "1d3",
						"message" => "bites at you",
						"status" => $statuses['zombie_virus'],
					),
				),
				'drops' => array(
					'chance' => 50,
					'name' => 'iron_dagger',
				),
			),
			"zombified_dog" => array(
				"description" => "<span style='color:red;'>A Partially-Decomposed Canine</span>", 
				"hp" => 12, 
				"xp" => 15,
				"attacks" => array(
					array(
						"dmg" => "1d3",
						"message" => "bites you",
					),
				),
			),
			"kluth" => array(
				"description" => "<span style='color:red;'><em>Kluthezcian</em>, the Dahk Magii</span>", 
				"hp" => 28, 
				"xp" => 100,
				"attacks" => array(
					array(
						"dmg" => "2d5",
						"message" => "hurls a dark orb of energy",
						"status" => $statuses['mana_burn'],
					),
					array(
						"dmg" => "1d3",
						"message" => "clubs you with staff",
					),
				),
			),
			"florin" => array(
				"description" => "<span style='color:red;'><em>Coorin Slaith</em>, the nightblood assassin</span>", 
				"hp" => 32, 
				"xp" => 125,
				"attacks" => array(
					array(
						"dmg" => "2d6",
						"message" => "stabs you with with twin, silver-tipped daggers",
					),
					array(
						"dmg" => "1d12",
						"message" => "is somehow behind you, backstabbing",
						"status" => $statuses['lesser_poison'],
					),
				),
				'drops' => array(
					'chance' => 100,
					'name' => 'silver_dagger',
				),
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
		
		if( !empty($this->data[$this->x][$this->y]['items']) )
		{
			$output .= "<br><span style='color: #993300;'>On the ground lays:</span><br>";
			
			foreach( $this->data[$this->x][$this->y]['items'] as $item )
			{
				$output .= $item['name'] . " ({$item['dmg']}) - <i>{$item['description']}</i><br>";
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
					$dmg = $this->get_damage($this->weapon['dmg']) + (2 * $this->level);
					$enemy['hp'] -= $dmg;
					$enemy['hp'] = ($enemy['hp'] < 0) ? 0 : $enemy['hp'];
					$message = is_array($this->weapon['message']) ? $this->weapon['message'][rand(0, count($this->weapon['message']) - 1)] : $this->weapon['message'];
					$output .= "<br>You $message {$enemy['description']} for $dmg damage with {$this->weapon['name']}. They have {$enemy['hp']} hp.";
					
					if( $enemy['hp'] > 0 )
					{
						// creature attack you
						$attack = $enemy['attacks'][rand(0, count($enemy['attacks']) - 1)];
						$dmg = $this->get_damage($attack['dmg']);
						$this->hp -= $dmg;
						$this->hp = ($this->hp < 0) ? 0 : $this->hp;
						$message = is_array($attack['message']) ? $attack['message'][rand(0, count($attack['message']) - 1)] : $attack['message'];
						$output .= "<br>" . $enemy['description'] . " $message for $dmg damage. You have {$this->hp} hp.";
						
						// Check status proc
						if (isset($attack['status']) && rand(1, 100) <= $attack['status']['chance'])
						{
							$status = $attack['status'];
							if ($status['stack'] == true || ($status['stack'] == false && array_search($status['name'], array_column($this->statuses, 'name')) === false)) { // Some statuses don't stack.
								$status['time_left'] = $status['duration'];
								$this->statuses[] = $status;
								$output .= "<br>" . $status['init_message'];
							}
						}
						
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
						
						// Handle dropping of items.
						if (isset($enemy['drops']) && rand(1, 100) <= $enemy['drops']['chance']) {
							$weapon = $this->weapons($enemy['drops']['name']);
							$this->data[$this->x][$this->y]['items'][] = $weapon;
							$output .= "<br>" . $enemy['description'] . " drops a " . $weapon['name'];
						}
						
						if( $this->xp >= $this->level * 50 )
						{
							$this->xp = 0;
							$this->level++;
							$this->hp = 25 + ($this->level * 5) + $this->level;
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
			$result += rand(0, $sides); // 0 Lets misses happen.
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