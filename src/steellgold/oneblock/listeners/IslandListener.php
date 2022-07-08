<?php

namespace steellgold\oneblock\listeners;

use JsonException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\entity\ItemMergeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\island\IslandFactory;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\task\BlockUpdateTask;

class IslandListener implements Listener {

	private array $blocks;

	public function __construct() {
		foreach (One::getInstance()->getIslandConfig()->get("points") as $block => $points) {
			var_dump("a");
			$this->blocks[$block] = $points;
		}
	}

	/**
	 * @throws JsonException
	 */
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$player->setGamemode(GameMode::SURVIVAL());
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
	}

	public function onPlayerTrample(EntityTrampleFarmlandEvent $event) {
		$player = $event->getEntity();
		if (str_starts_with($player->getWorld()->getFolderName(), "island-")) $event->cancel();
	}

	public function onInteract(PlayerInteractEvent $event) {
		if (!str_starts_with($event->getPlayer()->getWorld()->getFolderName(), "island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if ($island == null) return;
		if (!$island->hasMember($event->getPlayer()->getName())) {
			$event->cancel();
		}
	}

	public function onUse(PlayerItemUseEvent $event) {
		if (!str_starts_with($event->getPlayer()->getWorld()->getFolderName(), "island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if ($island == null) return;
		if (!$island->hasMember($event->getPlayer()->getName())) {
			$event->cancel();
		}
	}

	public function onPlace(BlockPlaceEvent $event) {
		if (!str_starts_with($event->getPlayer()->getWorld()->getFolderName(), "island-")) return;

		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		var_dump("aa");
		if ($island == null) return;
		var_dump("bb");
		if (!$island->hasMember($event->getPlayer()->getName())) {
			$event->cancel();
		}

		var_dump("dd");
		$idmeta = $event->getBlock()->getId() . ":" . $event->getBlock()->getMeta();
		var_dump($this->blocks);
		if (isset($this->blocks[$idmeta])) {
			var_dump($this->blocks[$idmeta]);
			$event->getPlayer()->sendMessage($this->blocks[$idmeta] . " points gagnées");
		}
	}


	/**
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function onBreak(BlockBreakEvent $event): void {
		if (!str_starts_with($event->getPlayer()->getWorld()->getFolderName(), "island-")) return;
		$island = IslandFactory::getIsland($event->getPlayer()->getWorld());
		if ($island == null) return;
		if (!$island->hasMember($event->getPlayer()->getName())) {
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

		$blocks = $session->getIsland()->getTier();
		if ($event->getBlock()->getPosition() == new Position(0, 38, 0, $player->getWorld())) {
			$session->getIsland()->addToObjective();
			$player->sendTip(str_replace(
				["{TIER_NAME}", "{TIER_LEVEL}", "{COUNT}"],
				[$island->getTier()->getName(), $island->getTier()->getId(), $island->getObjective()],
				One::getInstance()->getConfig()->get("messages")["xp-tip"] ?? "§a+1 | Tier {TIER_LEVEL}\n§f{COUNT} blocks breaked"
			));

			$block = $blocks->getChanceBlock()[0];
			One::getInstance()->getScheduler()->scheduleDelayedTask(new BlockUpdateTask($block, $event->getBlock()->getPosition()), 1);
		}
	}

	public function onDamage(EntityDamageByEntityEvent $event) {
		$player = $event->getEntity();
		$damager = $event->getDamager();
		if ($player instanceof Player and $damager instanceof Player) {
			if (
				str_starts_with($player->getWorld()->getFolderName(), "island-")
				and
				str_starts_with($damager->getWorld()->getFolderName(), "island-")) {
				$event->cancel();
			}
		}
	}

	public function onMoveCheckSessionVisit(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$session = One::getInstance()->getManager()->getSession($player->getName());

		if ($session == null) {
			$player->kick("Player have don't valid session");
			return;
		}

		if ($session->isInIsland() or str_starts_with($player->getWorld()->getFolderName(), "island-")) {
			if (str_starts_with($player->getWorld()->getFolderName(), "island-")) {
				$session->setIsInIsland(false);
			}
		}

		if ($session->isInVisit()) {
			if (!str_starts_with($player->getWorld()->getFolderName(), "island-")) {
				$session->setIsInVisit(false);
				$player->setGamemode(GameMode::SURVIVAL());
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
			if ($session->isInVisit()) {
				$player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
				return;
			}

			$player->teleport($island->getSpawn());
			$player->sendMessage(Text::getMessage("island_falled_reteleported"));
		}
	}

	public function itemVoid(EntityDamageEvent $event): void {
		$entity = $event->getEntity();
		$world = $entity->getWorld();

		if ($world == null) {
			return;
		}

		if (!str_starts_with($world->getFolderName(), "island-")) return;

		if ($event->getCause() == EntityDamageEvent::CAUSE_VOID) {
			$entity->teleport($world->getSpawnLocation());
			$event->cancel();
		}
	}

	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$player->setGamemode(GameMode::SURVIVAL());
		if (One::getInstance()->getManager()->hasSession($player->getName())) {
			$session = One::getInstance()->getManager()->getSession($player->getName());
			$session->setIsInIsland(false);
			$session->setIsInVisit(false);
			$session->getPlayer()->setGamemode(GameMode::SURVIVAL());
			One::getInstance()->getManager()->getSession($player->getName())->closeSession();
		}
	}
}