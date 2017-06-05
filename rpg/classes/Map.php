<?php

class Map
{
    private $data;

    public function Map()
    {
        $map = [];
        $map[0][0]['description'] = "<b>[Dusty Shack]</b><br>You're in an old dusty shack.<br>You see a door to the south.";
        $map[0][1]['description'] = "<b>[Choked Lawn]</b><br>You're standing in a lawn choked with dead weeds.<br>A shack is to the north.<br>The rest of the area is fenced in, aside from a small gap in the fence to the East which you might be able to pass through.";
        $map[1][1]['description'] = "<b>[Untamed Grasslands]</b><br>You've come upon a patch of untamed grass, likely a protected patch of land for wildlife.  Since the great calamity, however, it has gone largely unkempt.  Walking through the tall weeds, you periodically experience a strong sense of 'being watched', as if something were lurking in the tall brush.  Looking around you can see a small, fenced in area to the West, a path continuing to the East and a decrepit shack some ways to the North-West.";
        $map[2][1]['description'] = "<b>[Suth's Fortress Entrance]</b><br>You are standing in front of large fortress. A path runs to the West and a drawbridge leads inside the fortress to the East.";
        $map[3][1]['description'] = "<b>[Suth's Fortress]</b><br>You are in a dark stone fortress. A dark hallway meanders to the East. A drawbridge is seen to the West.";
        $map[4][1]['description'] = "<b>[Suth's Fortress Hallway]</b><br>You are in a long, dark hallway that ends at a intersection - it continues both North and South The fortress entrance is to the West.";
        $map[4][0]['description'] = "<b>[Suth's Fortress Hallway North]</b><br>The hallway stops at a giant oak door braced with corroded iron. It does not open. The hallway continues south.";
        $map[4][2]['description'] = "<b>[Suth's Fortress Hallway South]</b><br>Your boots clang lightly among the rough-hewn stone. Further South a spiral staircase descends into darkness. The hall continues to north.";
        $this->data = $map;
    }

    public function read($x = null, $y = null)
    {
        if (is_null($x) || is_null($y))
            return $this->data;
        else
            return $this->data[$x][$y];
    }

    public function write()
    {
        return true;
    }
}