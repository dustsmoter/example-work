<?php

require_once( ABSOLUTE_PATH . "classes/class.utility.php" );

class player extends utility
{
	private $user_data = array();	// User data from user table
	protected $entities = array();	// Array of entity objects owned by player
	
	function __construct( $username, $password )
	{
		global $db;
		parent::__construct($db);
		
		if( $this->login($username, $password) )
		{
			$this->retrieve_player_entities();
		} 	
	}
	
	public function get_entities()
	{
		return $this->entities;
	}
	
	public function login( $username, $password )
	{
		$results = $this->db->query( "SELECT * FROM user WHERE username = '%s' AND password = '%s'", $username, $password );
		
		if( !empty($results) )
		{
			$this->user_data = $results[0];
			return true;
		}
		
		return  false;
	}
	
	private function retrieve_player_entities()
	{
		global $entity_types;
		
		 $query = 'SELECT e.*, e.id AS entity_id, p.*, '.
		 		  '   c.*, c.id AS c_id, c.name AS c_name, c.description AS c_description, '.
		 		  '   i.*, i.id AS i_id, i.name AS i_name, i.description AS i_description, '.
		 		  '	  l.*, l.id AS l_id, l.name AS l_name, l.description AS l_description  '.
		 		  'FROM entity e '.
		 		  '	  LEFT JOIN creature c ON c.id = e.entity_type_id AND e.entity_type = "creature" '.
		 		  '	  LEFT JOIN item i ON i.id = e.entity_type_id AND e.entity_type = "item" '.
		 		  '	  LEFT JOIN location l ON l.id = e.entity_type_id AND e.entity_type = "location" '.
		 		  '   LEFT JOIN personality p ON p.user_id = %1$d AND p.entity_id = e.id '.
		 		  'WHERE e.user_id = %1$d';	 
				 
		$results = $this->db->query( $query, $this->user_data['id'] );
		echo $this->db->get_last_error();

		if( !empty($results) )
		{	
			// We want to load all the base entities first so we can assign owned one's later
			foreach( $results as $entity )
			{
				// Take creature|item|location name and turn it into just name
				if( !empty($entity['c_name']) )
				{
					$entity['name'] = $entity['c_name'];
					$entity['description'] = $entity['c_description'];
				}
				elseif( !empty($entity['i_name']) )
				{
					$entity['name'] = $entity['i_name'];
					$entity['description'] = $entity['i_description'];
				}
				elseif( !empty($entity['l_name']) )
				{
					$entity['name'] = $entity['l_name'];
					$entity['description'] = $entity['l_description'];
				}
				
				$entity_group[$entity['entity_id']] = $entity;
				
				if( empty($entity['owner_entity_id']) )
				{
					$entity_group[$entity['entity_id']]['owned_entities'] = array();
				}
				
				$entity_group[$entity['entity_id']] = $this->load_entity($entity_group[$entity['entity_id']]);
			}
			
			// Attach owned entities to base entity
			foreach( $entity_group as $entity )
			{
				if( isset($entity->owner_entity_id) && $entity->owner_entity_id )
				{
					$entity_group[$entity->owner_entity_id]->add_entity($entity);
					unset($entity_group[$entity->entity_id]);
				}
			}
			
			$entity_group = array_values($entity_group); // This simply resets array keys
			$this->entities = $entity_group;
			
			return true;
		}
		
		return  false;
	}
}

?>