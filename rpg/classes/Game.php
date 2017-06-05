<?php

class Game
{
    private $map;
    private $player;

    public function Game(Map $map, Player $player)
    {
        $this->map = $map;
        $this->player = $player;
    }
}