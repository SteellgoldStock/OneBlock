<?php

namespace steellgold\oneblock\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use steellgold\oneblock\commands\subs\IslandAcceptCommand;
use steellgold\oneblock\commands\subs\IslandCreateCommand;
use steellgold\oneblock\commands\subs\IslandDeleteCommand;
use steellgold\oneblock\commands\subs\IslandDenyCommand;
use steellgold\oneblock\commands\subs\IslandGoCommand;
use steellgold\oneblock\commands\subs\IslandHelpCommand;
use steellgold\oneblock\commands\subs\IslandInfoCommand;
use steellgold\oneblock\commands\subs\IslandInviteCommand;
use steellgold\oneblock\commands\subs\IslandKickCommand;
use steellgold\oneblock\commands\subs\IslandLeaveCommand;
use steellgold\oneblock\commands\subs\IslandMemberCommand;
use steellgold\oneblock\commands\subs\IslandPromoteCommand;
use steellgold\oneblock\commands\subs\IslandSetSpawnCommand;
use steellgold\oneblock\commands\subs\IslandTopCommand;
use steellgold\oneblock\commands\subs\IslandVisitCommand;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandCommand extends BaseCommand {

	const HULK = false;

	protected function prepare(): void {
		$this->registerSubCommand(new IslandCreateCommand("create", Text::getCommandDescription("create"))); # OK
		$this->registerSubCommand(new IslandGoCommand("go", Text::getCommandDescription("go"),["join"])); # OK
		$this->registerSubCommand(new IslandVisitCommand("visit", Text::getCommandDescription("visit"))); # OK
		$this->registerSubCommand(new IslandSetSpawnCommand("setspawn", Text::getCommandDescription("setspawn"))); # OK
		$this->registerSubCommand(new IslandTopCommand("top", Text::getCommandDescription("top"))); # OK
		$this->registerSubCommand(new IslandKickCommand("kick", Text::getCommandDescription("kick"))); # OK

		if(self::HULK){
			$this->registerSubCommand(new IslandMemberCommand("members", Text::getCommandDescription("member")));
			$this->registerSubCommand(new IslandInfoCommand("info", Text::getCommandDescription("info")));
			$this->registerSubCommand(new IslandDeleteCommand("delete", Text::getCommandDescription("delete"),["disband"]));

			$this->registerSubCommand(new IslandInviteCommand("invite", Text::getCommandDescription("invite")));
			$this->registerSubCommand(new IslandAcceptCommand("accept", Text::getCommandDescription("accept")));
			$this->registerSubCommand(new IslandDenyCommand("deny", Text::getCommandDescription("deny")));

			$this->registerSubCommand(new IslandHelpCommand("help", Text::getCommandDescription("help")));
			$this->registerSubCommand(new IslandLeaveCommand("leave", Text::getCommandDescription("leave")));
			$this->registerSubCommand(new IslandPromoteCommand("promote", Text::getCommandDescription("promote")));
			$this->registerSubCommand(new IslandDeleteCommand("demote", Text::getCommandDescription("demote")));
		}
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$sender->sendMessage(self::getHelp());
	}

	public static function getHelp() : string {
		$line = Text::getMessage("help-top");
		foreach ((new IslandCommand(One::getInstance(),"island"))->getSubCommands() as $subCommand) {
			$line .= "\n".str_replace(["{COMMAND}","{DESCRIPTION}"],[$subCommand->getName(),$subCommand->getDescription()],Text::getMessage("help-line"));
		}
		return $line;
	}
}