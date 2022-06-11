<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandPromoteCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		if(!isset($args["target"])) {
			$sender->sendMessage("§c/island promote <player>");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("promote")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("promote")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["promote", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		if ($session->getIsland()->getOwner() == $args['player']) {
			$sender->sendMessage(Text::getMessage("island_cant_promote", true, ["{PLAYER}"], [$args['player']]));
			return;
		}

		if ($session->getIsland()->getRank($args['player'], true) == 2) {
			$sender->sendMessage(Text::getMessage("island_cant_promote", true, ["{PLAYER}"], [$args['player']]));
			return;
		}

		$rank = $session->getIsland()->getRank($args['player'], true);
		$ra = $session->getIsland()->getRankById(($rank + 1));
		$session->getIsland()->setRank($args['player'], ($rank + 1));
		$sender->sendMessage(Text::getMessage("island_promoted", false, ["{PLAYER}", "{RANK}"], [$sender->getName(), $ra->getName()]));

		$p = One::getInstance()->getServer()->getPlayerByPrefix($args['player']);
		if ($p instanceof Player) {
			$p->sendMessage(Text::getMessage("island_player_promoted", false, ["{RANK}"], [$ra->getName()]));
		}
	}

	protected function prepare(): void {
		$this->registerArgument(0, new TargetArgument("player", false));
	}
}