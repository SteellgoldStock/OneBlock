<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class IslandSetSpawnCommand extends BaseSubCommand {

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if(!$sender instanceof Player){
			return;
		}
	}
}