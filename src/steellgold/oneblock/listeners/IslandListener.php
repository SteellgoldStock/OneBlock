<?php

namespace steellgold\oneblock\listeners;

use JsonException;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
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
			if ($island) {
				$is = One::getInstance()->getManager()->getIsland($identifier);
				if ($is instanceof Island) {
					$island = $is;
				} else {
					if (One::getInstance()->getManager()->islandFileExist($identifier)) {
						IslandFactory::restoreIsland($identifier);
						$island = One::getInstance()->getManager()->getIsland($identifier);
					} else $island = null;
				}
			} else $island = null;

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

	public function onPlayerTrample(EntityTrampleFarmlandEvent $event) {
		$player = $event->getEntity();
		if(str_starts_with($player->getWorld()->getFolderName(),"island-")) $event->cancel();
	}

	public function onInteract(PlayerInteractEvent $event) {
		if(!str_starts_with($event->getPlayer()->getWorld()->getFolderName(),"island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if($island == null) return;
		if(!$island->hasMember($event->getPlayer()->getName())){
			$event->cancel();
		}
	}

	public function onPlace(BlockPlaceEvent $event) {
		if(!str_starts_with($event->getPlayer()->getWorld()->getFolderName(),"island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if($island == null) return;
		if(!$island->hasMember($event->getPlayer()->getName())){
			$event->cancel();
		}
	}


	/**
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function onBreak(BlockBreakEvent $event): void {
		if(!str_starts_with($event->getPlayer()->getWorld()->getFolderName(),"island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if($island == null) return;
		if(!$island->hasMember($event->getPlayer()->getName())){
			$event->cancel();
			return;
		}

		$player = $event->getPlayer();
		$session = One::getInstance()->getManager()->getSession($player->getName());
		if ($session == null) {
			$player->kick("Player have don't valid session");
			$event->cancel();
			return;
		}

		$island = One::getInstance()->getManager()->getIsland($player->getWorld()->getFolderName());
		if ($island == null) {
			return;
		}

		if ($session->isInVisit() and in_array($player->getName(), $island->getVisitors())) {
			$event->cancel();
			return;
		}

		$blocks = One::getInstance()->getManager()->getTier();
		if ($event->getBlock()->getPosition() == new Position(0, 38, 0, $player->getWorld())) {
			$session->getIsland()->addToObjective($player);
			$player->sendTip("§f[§a+1§f]");

			$block = $blocks->getChanceBlock()[0];
			One::getInstance()->getScheduler()->scheduleDelayedTask(new BlockUpdateTask($block, $event->getBlock()->getPosition()), 1);
		}
	}

	public function onDamage(EntityDamageByEntityEvent $event){
		$player = $event->getEntity();
		$damager = $event->getDamager();
		if($player instanceof Player and $damager instanceof Player){
			if(
				str_starts_with($player->getWorld()->getFolderName(),"island-")
				and
				str_starts_with($damager->getWorld()->getFolderName(),"island-"))
			{
				$event->cancel();
			}
		}
	}

	public function onMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$session = One::getInstance()->getManager()->getSession($player->getName());
		if (!str_starts_with($player->getWorld()->getFolderName(), "island-")) return;

		if ($session == null) {
			$player->kick("Player have don't valid session");
			return;
		}

		$island = One::getInstance()->getManager()->getIsland($player->getWorld()->getFolderName());
		if ($event->getPlayer()->getPosition()->getY() < One::getInstance()->getIslandConfig()->get("reteleport_at_y")) {
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