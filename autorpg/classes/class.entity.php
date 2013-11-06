<?php

class entity  extends utility  
{
	protected $health;				// Current HP of entity
	protected $max_health;			// Max HP of entity
	protected $level;				// Current Level of entity
	protected $name;				// Name of entity
	protected $description;			// Description of entity
	protected $tags = array();		// The entity's tags
	
	public $entity_id;				// id of entity from entity table
	public $entity_type;			// Type of entity (creature, item, location)
	public $entity_type_id;			// The specific entity id from entity sub table (creature, item, location)
	
	public $owned_entities;			// Array of owned entity objects
	public $owner_entity_id;		// Simply states this object is owned by an entity
	
	function __construct( $entity_data )
	{
		global $db;
		parent::__construct($db);
		
		// If an entity has entities then assign them to the object list
		if( !empty($entity_data) )
		{
			if( !empty($entity_data['owned_entities']) )
			{
				$this->owned_entities = $entity_data['owned_entities'];
			}
			elseif( !empty($entity_data['owner_entity_id']) )
			{
				$this->owner_entity_id = $entity_data['owner_entity_id'];
			}
			
			$this->entity_id = $entity_data['entity_id'];
			$this->entity_type = $entity_data['entity_type'];
			$this->entity_type_id = $entity_data['entity_type_id'];
			$this->health = $entity_data['health'];
			$this->level = $entity_data['level'];
			$this->name = $entity_data['name'];
			$this->description = $entity_data['description'];
			
			$this->assign_entity_data($entity_data);
		}
	}
	
	public function attack( $entity, $attack )
	{
		// Entity you're attacking and your attack	
	}
	
	public function defend( $entity, $attack )
	{
		// Entity attacking you and it's attack
	}
	
	public function add_entity( $entity )
	{
		if( is_a($entity, "entity") )
		{
			$this->owned_entities[$entity->entity_type][] = $entity;
			return true;
		}
		
		return false;
	}
	
	public function get_items()
	{
		$items = array();
		
		if( isset($this->owned_entities['item']) )
		{
			$items = $this->owned_entities['item'];
		}
		
		return $items;
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function get_health()
	{
		return $this->health;
	}
	
	public function get_description()
	{
		return $this->description;
	}
	
	public function get_tags()
	{
		return $this->tags;
	}
	
	protected function assign_entity_data()
	{
		return false;
	}
}

?>