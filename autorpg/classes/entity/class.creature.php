<?php

class creature extends entity 
{
	protected $strength;
	protected $agility;
	protected $magic;
	protected $willpower;
	protected $constitution;
	
	protected $justice;
	protected $law;
	protected $greed;
	protected $religion;
	
	protected $char_name;
		
	function __construct( $entity_data )
	{
		parent::__construct($entity_data);
	}
	
	protected function assign_entity_data( $entity_data )
	{
		$this->strength = $entity_data['strength'];
		$this->agility = $entity_data['agility'];
		$this->magic = $entity_data['magic'];
		$this->willpower = $entity_data['willpower'];
		$this->constitution = $entity_data['constitution'];
		$this->justice = $entity_data['justice'];
		$this->law = $entity_data['law'];
		$this->greed = $entity_data['greed'];
		$this->religion = $entity_data['religion'];
		$this->char_name = $entity_data['char_name'];
	}
	
	public function get_strength( $modified = false )
	{
		return $this->strength;
	}
}

?>