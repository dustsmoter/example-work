<?php

class item extends entity 
{
	protected $damage;
	protected $defense;
		
	function __construct( $entity_data )
	{
		parent::__construct($entity_data);
	}
	
	function assign_entity_data( $entity_data )
	{
		$this->damage = $entity_data['damage'];
		$this->defense = $entity_data['defense'];
	}
}

?>