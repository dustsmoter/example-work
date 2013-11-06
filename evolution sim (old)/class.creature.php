<?php

$evolution = array( "eyes" => 5, "armor" => 3, "attack" => 3);

class creature
{
    var $x, $y;
    
    var $gender;
    
    var $dest_x, $dest_y;
    
    var $max_health = 100;
    var $health = 100;
    var $hunger = 0;
    var $key;
    var $family_id; // So you don't fuck your sister
    var $history = array(); // You always fight against a family you have fought with before
    var $baby = 0; // Basically a timer until you grow up
    var $dead = 0; // Same deal. how long until you rot
    
    // Parts
    var $evolution = array("eyes" => 1, "armor" => 0, "attack" => 0);
    
    var $winded = 0; // after a fight they need rest
    
    function creature()
    {
        $this->x = rand(0, MAP_WIDTH - 1);
        $this->y = rand(0, MAP_HEIGHT - 1);
        
        $this->rand_map_dest();
        
        $this->gender = rand(0, 1);
        
        $this->family_id = rand(0, 9999999);
        
        // Init evolve
       // $this->evolve();
    }
    
    function rand_map_dest()
    {
        $this->dest_x = rand(0, MAP_WIDTH - 1);
        $this->dest_y = rand(0, MAP_HEIGHT - 1);
    }
    
    function move()
    {
        if( $this->dest_x < $this->x )
        {
            $this->x--;
        }
        elseif( $this->dest_x > $this->x)
        {
            $this->x++;
        }
        
        if( $this->dest_y < $this->y )
        {
            $this->y--;
        }
        elseif( $this->dest_y > $this->y)
        {
            $this->y++;
        }
        
        if($this->x == $this->dest_x && $this->y == $this->dest_y)
        {
            $this->rand_map_dest();
        }
        
        /*$num = rand(1, 4);
        
        switch($num)
        {
            case 1:
            $this->x += rand(0, 2);
            if( $this->x > MAP_WIDTH - 1)
                $this->x = 0;
            break;
            
            case 2:
            $this->x -= rand(0, 2);
            if($this->x < 0)
                $this->x = MAP_WIDTH - 1;
            break;
            
            case 3:
            $this->y += rand(0, 2);
            if( $this->y > MAP_HEIGHT - 1)
                $this->y = 0;
            break;
            
            case 4:
            $this->y -= rand(0, 2);
            if($this->y < 0)
                $this->y = MAP_HEIGHT - 1;
            break;
        }*/
    }
    
    function evolve( $force = false)
    {
        global $evolution;
        
        if( rand(1, 4) == 1 || $force )
        {
            $evolution_keys = array_keys($evolution);
            $evolve_part = $evolution_keys[rand(0,count($evolution) - 1)];
            
            if( $this->evolution[$evolve_part] < $evolution[$evolve_part] )
                $this->evolution[$evolve_part]++; // Evolve random part
        }
    }
    
    function add_history($creature)
    {
        if( !in_array($creature->family_id, $this->history) )
        {
            $this->history[] = $creature->family_id;                            
        }
        
        if( !in_array($this->family_id, $creature->history) )
        {
            $creature->history[] = $this->family_id;                            
        }
    }
    
    function make_baby()
    {
        $this->baby = 5;
        $this->health = 0;
    }
    
    function fight_or_fuck(&$creatures)
    {
        if( $this->winded > 0 )
            $this->winded--;
        
        
        // battle
        foreach($creatures as $key => $creature)
        {
            if( $this->health > 25 && $creature->health > 0 && $creature->key != $this->key && $this->winded == 0 && $creature->winded == 0)
            {
                $a = $creature->x - $this->x;
                $b = $creature->y - $this->y;
                
                if( sqrt($a * $a + $b * $b) <= 1)
                {
                    // Fight or Fuck?
                    if( abs(array_sum($this->evolution) - array_sum($creature->evolution)) <= $evo_mate_dif && $this->gender != $creature->gender && $this->winded <= 3 && $creature->winded <= 3)
                    {
                        $this->winded = 5; // It takes work dammit!
                        
                        $family_id = rand(0,99999999);
                        $this->family_id = $family_id;
                        $creature->family_id = $family_id;
                        
                        return "fuck";
                    }
                    elseif( (rand(0,5) == 1 || in_array($creature->family_id, $this->history)) && array_sum($this->evolution) + rand(1,5) >= array_sum($creature->evolution) + rand(1,5) && $this->family_id != $creature->family_id) // little random
                    {   
                        // Put this in fUNCTION?
                        $this->add_history($creature);
                        
                        $damage = abs((($this->evolution['attack'] + 2) * 20) - (($creatures[$key]->evolution['armor'] + 1) * 10));
                        
                        if( $damage > 0 )
                            $creatures[$key]->health -= $damage;
                        
                        if( $creatures[$key]->health <= 0 )
                        {
                            $creatures[$key]->dead = 10;
                            $creatures[$key]->health = 0;
                            $creatures[$key]->winded = 5;
                            //$this->health = $this->max_health;
                            $this->hunger = 0;
                            $this->winded = 5;
                            
                            // He ate, he evolves
                            $this->evolve(true);
                        }
                    }
                    elseif($this->family_id != $creature->family_id)
                    {   
                        // Put this in fUNCTION?
                        $this->add_history($creature);
                        
                        $damage = abs((($creatures[$key]->evolution['attack'] + 2) * 20) - (($this->evolution['armor'] + 1) * 10));
                        
                        if( $damage > 0 )
                            $this->health -= $damage;
                        
                        if( $this->health <= 0 )
                        {
                            $this->dead = 10;
                            $this->health = 0;
                            $this->winded = 5;
                            //$creatures[$key]->health =$creatures[$key]->max_health;
                            $creatures[$key]->hunger = 0;
                            $creatures[$key]->winded = 5;
                            
                            // He ate, he evolves
                            $creatures[$key]->evolve(true);
                        }
                    }
                    
                    $stats["creatures_hunted"]++;
                    
                    // No moving after fight
                    return "fight";
                }
            }                
        }
        
        return false;
    }
    
    function resolve(&$map, &$creatures)
    {
        if( $this->health <= 0 )
            return;
        
        global $stats;
        
        $this->hunger++;

        // EYES-----------
        if( $this->evolution['eyes'] > 0 )
        {
            $eyes = $this->evolution['eyes'];
            $crop_map = get_range(
                                    &$map,
                                    $this->x - $eyes, $this->y - $eyes,
                                    $this->x + $eyes, $this->y + $eyes
                                  );
            
             foreach( $crop_map as $x => $col)
             {
                foreach( $col as $y => $tile )
                {
                    if( is_array($tile) && count($tile) > 0)
                    {                        
                        foreach( $tile as $obj)
                        {
                            if( $obj == FOOD && ($this->hunger >= 5 || $this->health < $this->max_health) )
                            {
                                // He saw food, move him there
                                $this->dest_x = $x;
                                $this->dest_y = $y;
                               
                                break 3;
                            }
                        }
                    }
                }
            }
        }
        //----------------
    
        
       $tile = $map[$this->x][$this->y];
        
       if( is_array($tile) && count($tile) > 0 )//&& $this->health < $this->max_health) // Only eat if you're hungry
       {
            foreach($tile as $obj_key => $obj)
            {
                // Little AI, just saying he needs to lose a bit of health before eating...%?
                if($obj == FOOD && ($this->hunger >= 5 || $this->health < $this->max_health))
                {
                    $this->health = $this->max_health;
                    $this->hunger = 0;
                    
                    // He ate, he evolves
                    $this->evolve();
                    
                    $stats['food_consumed']++;
                    
                    return $obj_key;
                }
            }
       }
       
        if( $this->hunger > 20)
            $this->health -= 20;
        elseif( $this->hunger > 10)
            $this->health -= 10;
        elseif( $this->hunger > 5 )
            $this->health -= 5;
            
        return false;
    }
}

?>