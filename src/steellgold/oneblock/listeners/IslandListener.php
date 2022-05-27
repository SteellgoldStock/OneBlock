<?php

namespace steellgold\oneblock\listeners;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Rank;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\One;

class IslandListener implements Listener {

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		if (!One::getInstance()->getManager()->hasSession($player->getName())) {
			$island = One::getInstance()->getManager()->hasIsland($player->getName()) ? One::getInstance()->getManager()->getIsland(One::getInstance()->getManager()->getIslandIdentifierByPlayer($player->getName())) : null;

			One::getInstance()->getManager()->addSession(new Session(
				$player,
				$island,
				$island?->getRank($player->getName()),
				false,
				false
			));
		}

		$session = One::getInstance()->getManager()->getSession($player->getName());
		if (One::getInstance()->getIslandConfig()->get("auto_island")) {
			if ($session->hasIsland()) {
				$island = $session->getIsland();
				$session->setIsInIsland(true);
				$player->teleport($island->getSpawn());
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event) {
		// TODO: Block action if doesn't permission
	}

	public function onPlace(BlockPlaceEvent $event) {
		// TODO: Block action if doesn't permission
	}

	public function onBreak(BlockBreakEvent $event) {
		// TODO: Generate block by tier
		// TODO: Block action if doesn't permission
	}

	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		if (One::getInstance()->getManager()->hasSession($player->getName())) {
			One::getInstance()->getManager()->getSession($player->getName())->closeSession();
		}
	}
}