<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;

class IslandInfoCommand extends BaseSubCommand {

	protected function prepare(): void {
		$this->registerArgument(0, new TextArgument("island",true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if(!isset($args["island"])){
			$sender->sendMessage("§cPlease enter a island name.");
			return;
		}
	}
}