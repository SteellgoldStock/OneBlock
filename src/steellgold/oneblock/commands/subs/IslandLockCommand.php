<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\instances\Rank;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandLockCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender);
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("lock")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("lock")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["lock", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		$island = $session->getIsland();
		if (!$island->isPublic()) {
			$sender->sendMessage(Text::getMessage("island_already_locked", true));
			return;
		}

		$island->setIsPublic(false);
		$sender->sendMessage(Text::getMessage("island_locked"));

		foreach ($island->getVisitors() as $visitor) {
			$visitor = One::getInstance()->getManager()->getSession($visitor);

			$visitor->setIsInVisit(false);

			if ($visitor->hasIsland()) {
				$visitor->getPlayer()->sendMessage(Text::getMessage("island_kick_to_locked"));
				$visitor->getPlayer()->teleport($visitor->getIsland()->getSpawn());
				$visitor->setIsInIsland(true);
				return;
			}

			$visitor->setIsInIsland(false);
			$visitor->getPlayer()->sendMessage(Text::getMessage("island_kick_locked"));
			$visitor->getPlayer()->teleport(One::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
		}
	}

	protected function prepare(): void {

	}
}