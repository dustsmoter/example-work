<?php

session_start();  

define('ABSOLUTE_PATH', dirname(__FILE__));

// DEBUG MODE
if( !empty($_SESSION['debug']) || isset($_GET['debug']) )
{
    $_SESSION['debug'] = !isset($_GET['debug']) ? true : $_GET['debug'];
    $debug = $_SESSION['debug'];
}
else 
{
    $debug = false;
}

// So no one see's anything
if( !$debug )
{
    //die("Site is currently unavailable");
}

// Initialize memcached
$mc = new Memcache;
$mc->connect('localhost', 11211);

// Load DB class
require_once($config['shared_path'] . "/includes/class.db.php");
$db = new database($mc, "localhost", "db", "user", "pass");

// Geo user and detect sub domain
require_once($config['shared_path'] . "/includes/class.geo.php");
$geo = new geo($db, $_SERVER);

// Load user_data if already logged in
require_once($config['shared_path'] . "/includes/class.user.php");
if( !empty($_SESSION['user']) )
{
	$user = new user($db, $_SESSION['user']);
}
else
{
	$user = new user($db);
}

// Get user's sub domain 
$sub = str_replace($config['domain'], "", $_SERVER["HTTP_HOST"]);
if( !empty($sub) )
{
    $sub = explode(".", $sub);
    $geo->get_sub_region($sub[0]);
}
elseif( !$geo->get_nearest_region() )
{
    // Couldn't locate them. Show a default page? Default region?
} 

// Load stat tracking
require($config['shared_path'] . "/includes/class.stats.php");
$stats = new stats($db, $_SERVER, $config['id']);

// Utility functions
function debug_print_array( $array )
{
    if( !empty($array) )
    {
        echo "<pre>" . print_r($array, true) . "</pre>";
    }
}

function get_file( $filename )
{
    if( !empty($filename) )
    {
        if( $data = file_get_contents($filename) )
        {
            return $data;
        }
    }
    
    return false;
}

function put_file( $filename, $contents )
{
    if( !empty($filename) )
    {
        if( file_put_contents($filename, $contents) )
        {
            return true;
        }
    }
    
    return false;
}

// Valid field types = numeric, url, currency, varchar, state, phone, email
function req_fields( $post_data, $fields, $required = null, $sanitize = true )
{
    $errors = array();
    
    // Verify all fields are present
    foreach( $fields as $field => $type )
    {
        $flag_error = false;

        if( $sanitize )
            $post_data[$field] = addslashes(strip_tags(trim($post_data[$field])));

        if( !empty($post_data[$field]) )
        {
            switch( $type )
            {
                case 'numeric':
                    if( !is_numeric($post_data[$field]) )
                        $flag_error = true;
                break;

                case 'url':
                case 'currency':
                    if( !strstr($post_data[$field], ".") )
                        $flag_error = true;
                break;

                case 'varchar':

                break;

                case 'state':
                    if( strlen($post_data[$field]) != 2 )
                        $flag_error = true;
                break;

                case 'phone':
                    if( strlen($post_data[$field]) < 7 )
                        $flag_error = true;
                break;

                case 'email':
                    if( !strstr($post_data[$field], "@") || !strstr($post_data[$field], ".") )
                        $flag_error = true;
                break;
            }

            if( $flag_error )
            {
                $errors[] = "Field $field is not formatted correctly";
            }
        }
        else 
        {
            // Make sure it's in the required array before setting error
            if( !empty($required) && in_array($field, $required) )
            {
                $errors[] = "Missing $field field";   			
            }
        }
    }
    
    return empty($errors) ? false : $errors;
}

function remove_http( $domain )
{
    if( !empty($domain) )
    {
        return trim(str_replace("http://", "", $domain), "/");
    }
    
    return false;
}

?>