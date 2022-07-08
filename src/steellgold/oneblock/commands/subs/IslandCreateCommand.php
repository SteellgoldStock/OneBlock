<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\island\IslandFactory;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandCreateCommand extends BaseSubCommand {

	/**
	 * @throws JsonException
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender);
		if ($session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("already_in_island", true));
			return;
		}

		if (
			$session->getTimer() == 0
			or
			$session->isEndedTimer()
		) {
			IslandFactory::createIsland($sender, One::getInstance()->getManager()->getTier());
		} else {
			$time = $session->getTimer() - time();
			$minutes = floor($time / 60);
			$seconds = $time % 60;
			$sender->sendMessage(Text::getMessage("timer_create", true, ["{MINUTES}", "{SECONDS}"], [$minutes, $seconds]));
		}
	}

	protected function prepare(): void {

	}
}