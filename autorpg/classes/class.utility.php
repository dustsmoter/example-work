<?php

class utility
{
	protected $db;
							
	function __construct( &$db )
	{
		if( is_object($db) )
		{
			$this->db = $db;
		}
	}
	
	protected function load_entity( $entity )
	{
		if( !empty($entity) )
		{
			$class_name = $entity['entity_type'];
			$entity_override = strtolower(str_replace(" ", "_", $entity['name']));
			
			$class_override_location = ABSOLUTE_PATH . "classes/entity/$class_name/class.$entity_override.php";
			
			$class_location =  ABSOLUTE_PATH . "classes/entity/class.$class_name.php";
			
			require_once($class_location);
			
			if( file_exists($class_override_location) )
			{
				require_once($class_override_location);
				return new $entity_override($entity);
			}
			else
			{
				if( file_exists($class_location) )
				{
					return new $class_name($entity);
				}
				else 
				{
					echo "Could not find $class_location<br>";
				}	
			}
		}
		
		return false;
	}
}

?>