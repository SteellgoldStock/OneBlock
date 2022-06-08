<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandGoCommand extends BaseSubCommand {

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender);
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		$sender->teleport($session->getIsland()->getSpawn());
		$message = str_replace(
			["{ONWER}", "{X}", "{Y}", "{Z}"],
			[
				$session->getIsland()->getOwner(),
				$session->getIsland()->getSpawn()->getX(),
				$session->getIsland()->getSpawn()->getY(),
				$session->getIsland()->getSpawn()->getZ()
			],
			One::getInstance()->getConfig()->get("messages")["island_teleport"]
		);

		switch (One::getInstance()->getConfig()->get("messages")["island_teleport_type"]) {
			case "tip":
				$sender->sendTip($message);
				break;
			case "popup":
				$sender->sendPopup($message);
				break;
			case "message":
				$sender->sendMessage(One::getInstance()->getConfig()->get("messages")["prefix"]["success"] . $message);
				break;
		}
	}
}