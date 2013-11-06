<?php 

define('ABSOLUTE_PATH', 'c:/Users/Peterson/Desktop/Development/autorpg/');

// load Smarty library
require_once('Smarty.class.php');

$smarty = new Smarty;

$smarty->template_dir = ABSOLUTE_PATH . 'smarty/templates';
$smarty->config_dir = ABSOLUTE_PATH . 'smarty/configs';
$smarty->cache_dir = 'c:/Users/Peterson/Desktop/Development/smarty/cache';
$smarty->compile_dir = 'c:/Users/Peterson/Desktop/Development/smarty/templates_c';

// load Smarty library
require_once(ABSOLUTE_PATH . "classes/class.db.php");
$db = new database( "127.0.0.1", "rpg", "root", "");

// Utility functions
function __autoload( $class_name)  
{
    require_once(ABSOLUTE_PATH . "classes/class.$class_name.php");
}

function debug_print_array( $array )
{
	if( !empty($array) )
	{
		echo "<pre>" . print_r($array, true) . "</pre>";
	}
}

?>