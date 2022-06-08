<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use steellgold\oneblock\commands\IslandCommand;

class IslandHelpCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$sender->sendMessage(IslandCommand::getHelp());
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}