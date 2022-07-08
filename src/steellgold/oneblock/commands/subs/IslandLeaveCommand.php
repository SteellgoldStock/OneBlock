<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandLeaveCommand extends BaseSubCommand {

	/**
	 * @throws JsonException
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if ($session->getIsland()->getOwner() == $sender->getName()) {
			$sender->sendMessage(Text::getMessage("island_owner_left", true));
			return;
		}

		$session->setIsInIsland(false);
		$session->getIsland()->delMember($sender->getName());
		$session->setIsland(null);
		$sender->sendMessage(Text::getMessage("island_left"));
		$session->getPlayer()->setGamemode(GameMode::SURVIVAL());
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}