<?php

error_reporting(E_ERROR);

header("Expires: Sat, 1 Jan 2005 00:00:00 GMT"); 
header("Last-Modified: ".gmdate( "D, d M Y H:i:s")."GMT"); 
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 

function debug_print_array( $data )
{
    if( is_array($data) || is_object($data) )
    {
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

require_once("class.creature.php");

session_start();
$stats = array();

require_once("class.settings.php");

$stats["creatures_start"] = $num_start_creatures;
$stats["turns"] = $turns;

function get_range( &$map, $x_start, $y_start, $x_end, $y_end )
{
    if($x_start < 0 )
        $x_start = 0;
    if($x_end > MAP_WIDTH - 1)
        $x_end = MAP_WIDTH - 1;
        
    if($y_start < 0 )
        $y_start = 0;
    if($y_end > MAP_HEIGHT - 1)
        $y_end = MAP_HEIGHT - 1;
    //echo "X1: $x_start, X2: $x_end / Y1: $y_start, Y2: $y_end<br>";
    $crop_map = array();
    
     for($y=$y_start; $y<=$y_end; $y++)
    {
        for($x=$x_start; $x<= $x_end; $x++)   
         {
             $crop_map[$x][$y] = $map[$x][$y];
         }   
    }
    
    return $crop_map;
}

function seed_obj( &$map, $obj_type, $chance_to_spawn)
{
    for( $y=0; $y< MAP_HEIGHT; $y++)
    {
       for( $x=0; $x< MAP_WIDTH; $x++)    
        {
            foreach($map as $tile)
            {
                if($tile == $obj_type)
                    break 2;
            }
            
            if( rand(1,1000) <= $chance_to_spawn)
            {
                $map[$x][$y][] = $obj_type;
            }
        } 
    }
}

function init_map(&$map)
{
     for( $y=0; $y< MAP_HEIGHT; $y++)
    {
       for( $x=0; $x< MAP_WIDTH; $x++)    
        {
                $map[$x][$y][] = 0;
        }
    }
}

if( empty($_SESSION['creatures']) || empty($_SESSION['map']) || !empty($_REQUEST['reset']) )
{
    // Generate Map
    init_map($map);
    seed_obj($map, FOOD, $init_chance_food);
    
    // Generate Creatures
    for($i=0; $i < $num_start_creatures; $i++)
    {
        $creature = new creature();
        $creatures[] = $creature;
        $creature->key = max(array_keys($creatures));
    }
}
else
{
    $map = $_SESSION['map'];
    $creatures = $_SESSION['creatures'];
}

// Simulate
for($i=0; $i<$turns; $i++)
{
    // World effects
    seed_obj($map, FOOD, $chance_food);
    
    $creatures_to_add = array();
    
    foreach( $creatures as $key => $creature )
    {
        if( $creature->baby > 0 )
        {
            $creature->baby--;
            
            if( $creature->baby <= 0 ) // If we just turned it into baby, give it health
            {
                $creature->health = $creature->max_health;
            }
            
            continue;
        }
        
        if( $creature->dead > 0 )
            $creature->dead--;
        
        if( $creatures[$key]->health > 0)
        {
            // Only move if he didn't fight
            $fof = $creature->fight_or_fuck($creatures);
            if($fof == "fuck")
            {
                $new_creature = new creature();
                $new_creature->x = $creature->x;
                $new_creature->y = $creature->y;
                $new_creature->family_id = $creature->family_id;
                
                $new_creature->make_baby();
                
                $creatures_to_add[] = $new_creature;
                $new_creature->key = max(array_keys($creatures));
                unset($new_creature);
            }
            elseif( $fof != "fight" )
            {
                $creature->move();
            }
            
            if( $tile_eaten = $creature->resolve($map, $creatures) )
            {
                unset($map[$creature->x][$creature->y][$tile_eaten]);   
            }
        }
    }
    
    // Append on babies
    if( is_array($creatures_to_add) && count($creatures_to_add) > 0 )
    {
        foreach($creatures_to_add as $creature)
        {
            $creatures[] = $creature;
        }
        
        unset($creatures_to_add);
    }
}

$_SESSION['map'] = $map;
$_SESSION['creatures'] = $creatures;

// Stats
foreach( $creatures as $creature )
{
    if( $creature->health > 0)
    {
        $stats["creatures_alive"]++;
        
        foreach($creature->evolution as $part => $val)
        {
            $stats["survived"]["parts"]["{$part}_{$val}"]++;
            $stats["survived"]["evolution_cycle"][(array_sum($creature->evolution)) . "_parts"]++;
        }
    }
    else
    {
        $stats["creature_dead_count"]++;
    }
}

for( $y=0; $y< MAP_HEIGHT; $y++)
{
   for( $x=0; $x< MAP_WIDTH; $x++)    
    {
        if( is_array($map[$x][$y]) && count($map[$x][$y]) > 0)
        {
            foreach( $map[$x][$y] as $obj)
            {
                if( $obj == FOOD)
                {
                    $stats["food_left"]++;
                }
            }
        }
    }
}

//arsort($stats["survived"]["parts"]);
//arsort($stats["survived"]["evolution_cycle"]);
//ksort($stats);

//if( is_array($stats['creatures_alive']))
    //rsort($stats['creatures_alive']);

//debug_print_array($stats);

$im = @ImageCreateTrueColor (MAP_WIDTH * 20, MAP_HEIGHT * 20) or die ("Cannot Initialize new GD image stream");
$background_color = ImageColorAllocate ($im, 0, 0, 0);
$grass_color = ImageColorAllocate ($im, 10, 150, 20);

for( $y=0; $y< MAP_HEIGHT; $y++)
{
   for( $x=0; $x< MAP_WIDTH; $x++)    
    {
        $tile = $map[$x][$y];
        
        if( is_array($tile) && count($tile) > 0)
        {                        
            foreach( $tile as $obj)
            {
                if( $obj == FOOD)
                {
                    imagefilledrectangle($im, $x * 20, $y * 20, ($x * 20) + 20, ($y * 20) + 20, $grass_color);
                }
            }
        }
   }
}


$eye_color = imageColorAllocate ($im, 255, 255, 255);
$armor_color = imagecolorallocate($im, 250, 236, 50);
$attack_color = imagecolorallocate($im, 220, 220, 220);
$health_color = imagecolorallocate($im, 205, 50, 10);
foreach( $creatures as $creature )
{
    if( $creature->health > 0)
    {
        if( $creature->gender == 0 )
            $color = imageColorAllocate ($im, 0, 0, 255);
        else
            $color = imageColorAllocate ($im, 219, 112, 147);
    }
    else
    {
        $color = ImageColorAllocate ($im, 255, 0, 0);
    }
    
    $tile_x = $creature->x * 20;
    $tile_y = $creature->y * 20;
    
    if( $creature->baby <= 0 )
    {
        if( $creature->dead <= 3 && $creature->health <= 0 )
                $color = imageColorAllocate ($im, 139, 69, 19); // Rotting
        elseif( $creature->dead <= 6 && $creature->health <= 0 )
                $color = imageColorAllocate ($im, 119, 136, 153); // Rotting
    }
    
    if( $creature->dead > 0 || $creature->health > 0 || $creature->baby > 0 )
    {
        if( $creature->baby <= 0 )
        {
            imagefilledrectangle($im, $tile_x, $tile_y, $tile_x+ 15, $tile_y + 15, $color); // creature
            imagefilledrectangle($im, $tile_x, ($tile_y + 15), $tile_x + 2, ($tile_y + 15) - ($creature->health / 10), $health_color); // health bar
        }
        else
        {
            imagefilledellipse($im, $tile_x + 8, $tile_y + 8, 10, 10, $color);
            $stats['baby_count']++;
        }
                
        if($creature->evolution['eyes'] > 1 )
        {
            imagefilledrectangle($im, $tile_x + 1, $tile_y + 1, $tile_x+ 4, $tile_y + 4, $eye_color);
            imagefilledrectangle($im, $tile_x + 11, $tile_y + 1, $tile_x+ 14, $tile_y + 4, $eye_color);
        }
     
        if($creature->evolution['armor'] > 0 )
        {
            imagerectangle($im, $tile_x, $tile_y, $tile_x+ 15, $tile_y + 15, $armor_color);
        }
        
        if($creature->evolution['attack'] > 0 )
        {
            imageline($im, $tile_x + 4, $tile_y + 8, $tile_x + 7, $tile_y + 12, $attack_color);
            imageline($im, $tile_x + 10, $tile_y + 8, $tile_x + 13, $tile_y + 12, $attack_color);
        }
    }
}

// Replace path by your own font path
$font = 'arial.ttf';
$text = '';
if( 1==2)
{
    imagettftext($im, 20, 5, 10, 80, $health_color, $font, $text);
    imagettftext($im, 20, 5, 11, 82, $health_color, $font, $text);
}

$file = "img/map_" . session_id() . "_" . rand(0,9999999) . ".png";
imagepng($im, $file);
imagedestroy($im);

echo $file. "|" . $stats["creatures_alive"] . "|" . $stats['baby_count'] . "|" . rand(0,999999999);

?>