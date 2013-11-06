<?php 

// Config
$config['shared_path'] = "/home/gemlevy/public_html";

// Pull out the controller requested
$controller = trim(substr($_SERVER['REQUEST_URI'], 1), "/");

// Load controller and view (could be re-worked)
if( file_exists($config['shared_path'] . "/application/controllers/$controller.php") )
{
    require_once($config['shared_path'] . "/application/controllers/$controller.php");
    
    // Load the view from domain if it exists. Otherwise use a default one from shared code
    if( file_exists("/application/views/$controller.php") )
    {
        require_once($config['shared_path'] . "/application/views/$controller.php");
    }
    elseif( file_exists($config['shared_path'] . "/application/views/$controller.php") )
    {
        require_once($config['shared_path'] . "/application/views/$controller.php"); 
    }
    else
    {
        die("Cannot load page.");
    }
}
else
{
    // Load the index page by default
    require_once($config['shared_path'] . "/application/controllers/default.php");
    require_once("/application/views/default.php");
}

?>
