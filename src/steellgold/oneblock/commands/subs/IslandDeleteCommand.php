<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandDeleteCommand extends BaseSubCommand {

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("delete")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank){
				if($rank->hasPermission("kick")){
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission",true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["kick", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}
	}
}