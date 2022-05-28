<?php

namespace steellgold\oneblock\listeners;

use JsonException;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\GameMode;
use pocketmine\world\Position;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\island\IslandFactory;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\task\BlockUpdateTask;

class IslandListener implements Listener {

	/**
	 * @throws JsonException
	 */
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		if (!One::getInstance()->getManager()->hasSession($player->getName())) {
			$identifier = One::getInstance()->getManager()->getIslandIdentifierByPlayer($player->getName());
			$island = One::getInstance()->getManager()->hasIsland($player->getName());
			if($island){
				$is = One::getInstance()->getManager()->getIsland($identifier);
				if($is instanceof Island){
					$island = $is;
				}else{
					if(One::getInstance()->getManager()->islandFileExist($identifier)){
						IslandFactory::restoreIsland($identifier);
						$island = One::getInstance()->getManager()->getIsland($identifier);
					}else $island = null;
				}
			}else $island = null;

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
		$player = $event->getPlayer();
		$session = One::getInstance()->getManager()->getSession($player->getName());
		if($session == null) {
			$player->kick("Player have don't valid session");
			$event->cancel();
			return;
		}

		$island = One::getInstance()->getManager()->getIsland($player->getWorld()->getFolderName());
		if($island == null){
			return;
		}

		if($session->isInVisit() and in_array($player->getName(),$island->getVisitors())){
			$event->cancel();
			return;
		}

		$blocks = One::getInstance()->getManager()->getTier();
		if($event->getBlock()->getPosition() == new Position(0, 38, 0, $player->getWorld())){
			$session->getIsland()->addToObjective($player);
			$player->sendTip("§f[§a+1§f]");
			One::getInstance()->getScheduler()->scheduleDelayedTask(new BlockUpdateTask($blocks->getChanceBlock()[0], $event->getBlock()->getPosition()),3);
		}
		// TODO: Block action if doesn't permission
	}

	public function onDeath(PlayerDeathEvent $event) {
		// TODO: Respawn in island (if true in config)
	}

	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$session = One::getInstance()->getManager()->getSession($player->getName());
		if(!str_starts_with($player->getWorld()->getFolderName(),"island-")) return;

		if($session == null) {
			$player->kick("Player have don't valid session");
			return;
		}

		$island = One::getInstance()->getManager()->getIsland($player->getWorld()->getFolderName());
		if($event->getPlayer()->getPosition()->getY() < One::getInstance()->getIslandConfig()->get("reteleport_at_y")){
			$player->teleport($island->getSpawn());
			$player->sendMessage(Text::getMessage("island_falled_reteleported"));
		}
	}

	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$player->setGamemode(GameMode::SURVIVAL());
		if (One::getInstance()->getManager()->hasSession($player->getName())) {
			$session = One::getInstance()->getManager()->getSession($player->getName());
			$session->setIsInIsland(false);
			$session->setIsInVisit(false);
			One::getInstance()->getManager()->getSession($player->getName())->closeSession();
		}
	}
}