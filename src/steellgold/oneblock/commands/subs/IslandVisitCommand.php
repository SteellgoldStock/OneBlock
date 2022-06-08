<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandVisitCommand extends BaseSubCommand {

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->registerArgument(0, new TargetArgument("player", false));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($args["player"]);
		$vsession = One::getInstance()->getManager()->getSession($sender->getName());

		if ($session == null) {
			$sender->sendMessage(Text::getMessage("player_not_found", true, ["{PLAYER}"], [$args["player"]]));
			return;
		}

		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("player_island_not_exist", true, ["{PLAYER}"], [$sender->getName()]));
			return;
		}

		if (!$session->getIsland()->isPublic()) {
			$sender->sendMessage(Text::getMessage("island_cant_visit", true, ["{OWNER}"], [$session->getIsland()->getOwner()]));
			return;
		}

		if (count($session->getIsland()->getVisitors()) == One::getInstance()->getIslandConfig()->get("visitor_limit")) {
			$sender->sendMessage(Text::getMessage("island_max_visitors", true, ["{NOW}", "{MAX}"], [count($session->getIsland()->getVisitors()), One::getInstance()->getIslandConfig()->get("max-visitors")]));
			return;
		}

		$session->getIsland()->addVisitor($sender->getName());
		$vsession->setIsInVisit(true);
		$vsession->setIsInIsland(true);

		$sender->setGamemode(match ((int)One::getInstance()->getIslandConfig()->get("visitor_gamemode_spectator")) {
			0 => GameMode::SURVIVAL(),
			1 => GameMode::CREATIVE(),
			3 => GameMode::SPECTATOR(),
			default => GameMode::ADVENTURE()
		});
		$sender->sendMessage(Text::getMessage("island_visit_teleported", false, ["{OWNER}", "{GAMEMODE}"], [$session->getIsland()->getOwner(), $sender->getGamemode()->getEnglishName()]));
		$sender->teleport($session->getIsland()->getSpawn());
	}
}