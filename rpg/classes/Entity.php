<?php

class Entity
{
    private $hp = 0;
    private $x = 0;
    private $y = 0;

    public function Entity($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getHp()
    {
        return $this->hp;
    }

    public function setHp($hp)
    {
        $this->hp = $hp;
    }
}