<?php
error_reporting(E_ALL);
require_once("/common.inc");

$player = new player("bpeterson", "password");
$narrate = new narrate();
$quest = new quest();

$game = new game( $player, $quest, $narrate );

$smarty->display("index.tpl");

?>