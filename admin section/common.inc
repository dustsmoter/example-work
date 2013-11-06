<?php

session_start();

error_reporting(E_ERROR);

define('ABSOLUTE_PATH', dirname(__FILE__));

// Load DB class
require_once("classes/class.db.php");

// Different database configurations for live and local
if( $_SERVER['SERVER_NAME'] == "localhost" )
{
    $db = new database( "localhost", "soda", "root", "");
}
else
{
    $db = new database( "loclahost", "db", "user", "pass");
}

// Load user_data if already logged in
require_once("classes/class.user.php");
if( !empty($_SESSION['user']) )
{
    $user = new user($db, $_SESSION['user']);
}
else
{
    $user = new user($db);
}

// Utility functions
function debug_print_array( $array )
{
    if( !empty($array) )
    {
            echo "<pre>" . print_r($array, true) . "</pre>";
    }
}

function generate_password( $length )
{ 
	$password = "";
	
	srand((double)microtime()*1000000); 
	 
	$vowels = array("a", "e", "i", "o", "u"); 
	$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", 
	"cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl"); 
	 
	$num_vowels = count($vowels); 
	$num_cons = count($cons); 
	 
	for($i = 0; $i < $length; $i++){ 
		$password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)]; 
	} 
	
	$substr = substr($password, 0, $length);
	
	return $substr . (string)rand(100,999); 
}  

?>