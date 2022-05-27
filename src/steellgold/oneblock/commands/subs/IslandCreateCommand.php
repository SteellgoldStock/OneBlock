<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\island\IslandFactory;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandCreateCommand extends BaseSubCommand {

	protected function prepare(): void {

	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if(!$sender instanceof Player){
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender);
		if($session->hasIsland()){
			$sender->sendMessage(Text::getMessage("already_in_island",true));
			return;
		}

		IslandFactory::createIsland($sender, One::getInstance()->tiers[1]);
	}
}