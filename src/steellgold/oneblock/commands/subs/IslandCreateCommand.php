<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class IslandCreateCommand extends BaseSubCommand {

	protected function prepare(): void {

	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if(!$sender instanceof Player){
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}
	}
}