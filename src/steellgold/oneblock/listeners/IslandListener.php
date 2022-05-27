<?php

namespace steellgold\oneblock\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class IslandListener implements Listener {
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();

		$baseX = $player->getPosition()->getX() >> 4;
		$baseZ = $player->getPosition()->getZ() >> 4;
		$player->sendTip("§aX: §f$baseX" . " §aZ: §f$baseZ");
	}
}