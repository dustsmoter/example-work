<?php

// Map Constants
define( "FOOD", 1);
define( "MAP_WIDTH", 40);
define( "MAP_HEIGHT", 40);

$map = array();
$creatures = array();

// World Options
$turns = 1;
$init_chance_food = 30;
$chance_food = 3;
$num_start_creatures = 40;
$evo_mate_dif = 10; // They need to be within 5 to mate

$stats['baby_count'] = 0;

?>