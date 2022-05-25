<?php

namespace steellgold\oneblock\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class IslandCommand extends BaseCommand {

	protected function prepare(): void {
		$this->registerSubCommand(new IslandCreateCommand());
		$this->registerSubCommand(new IslandGoCommand());
		$this->registerSubCommand(new IslandMemberCommand());
		$this->registerSubCommand(new IslandInfoCommand());
		$this->registerSubCommand(new IslandDeleteCommand());
		$this->registerSubCommand(new IslandInviteCommand());
		$this->registerSubCommand(new IslandAcceptCommand());
		$this->registerSubCommand(new IslandDenyCommand());
		$this->registerSubCommand(new IslandTopCommand());
		$this->registerSubCommand(new IslandHelpCommand());
		$this->registerSubCommand(new IslandLeaveCommand());
		$this->registerSubCommand(new IslandPromoteCommand());
		$this->registerSubCommand(new IslandKickCommand());
		$this->registerSubCommand(new IslandVisitCommand());
		$this->registerSubCommand(new IslandSetSpawnCommand());
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {

	}

	public static function getHelp(){
		return "";
	}
}