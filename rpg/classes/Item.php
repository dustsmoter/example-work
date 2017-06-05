<?php

class Item
{
    private $name = "";
    private $description = "";
    private $damage;

    public function Item()
    {
        $this->damage = new Damage();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}