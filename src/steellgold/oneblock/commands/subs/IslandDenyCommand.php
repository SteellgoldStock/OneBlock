<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandDenyCommand extends BaseSubCommand {

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

		if ($session->hasInvitation()) {
			if ($session->denyInvitation()) {
				if ($session->getInviter()->isOnline()) {
					$session->getInviter()->sendMessage(Text::getMessage("island_invited_refused", true, ["{PLAYER}"], [$sender->getName()]));
				}

				$sender->sendMessage(Text::getMessage("island_invited_deny", true, ["{INVITER}"], [$session->getInviter()->getName()]));
				$session->removeInviteCache();
			} else {
				$sender->sendMessage(Text::getMessage("island_expired", true));
			}
		}else{
			$sender->sendMessage(Text::getMessage("island_expired", true));
		}
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}