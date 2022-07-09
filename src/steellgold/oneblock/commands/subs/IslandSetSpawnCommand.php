<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandSetSpawnCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("setspawn")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("setspawn")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["setspawn", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if ($session->isInVisit()) {
			$sender->sendMessage(Text::getMessage("commands_visit", true));
			return;
		}

		if ($session->isInIsland()) {
			$sender->getWorld()->setSpawnLocation($sender->getPosition());
			$session->getIsland()->setSpawn([
				"X" => $sender->getPosition()->getX(),
				"Y" => $sender->getPosition()->getY(),
				"Z" => $sender->getPosition()->getZ(),
			]);
			$sender->sendMessage(Text::getMessage("island_setspawn_success", false, ["{OWNER}", "{X}", "{Y}", "{Z}"], [$session->getIsland()->getOwner(), $sender->getPosition()->getX(), $sender->getPosition()->getY(), $sender->getPosition()->getZ()]));
		} else {
			$sender->sendMessage(Text::getMessage("setspawn_not_in_island", true));
			return;
		}
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}