<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandAcceptCommand extends BaseSubCommand {

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
			if ($session->acceptInvitation()) {
				$session->setIsland(One::getInstance()->getManager()->getIsland($session->getCurrentInvite()["id"]));
				$session->getIsland()->addMember($sender, 1);
				foreach ($session->getIsland()->getMembers() as $member => $rankId) {
					$pmember = Server::getInstance()->getPlayerByPrefix($member);
					if ($pmember->isOnline()) {
						$pmember->sendMessage(Text::getMessage("island_invited_accepted", false, ["{PLAYER}"], [$sender->getName()]));
					}
				}

				$sender->sendMessage(Text::getMessage("island_invited_accepted", false, ["{OWNER}"], []));
				$session->removeInviteCache();
			} else {
				$sender->sendMessage(Text::getMessage("island_expired", true));
			}
		} else {
			$sender->sendMessage(Text::getMessage("island_expired", true));
		}
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}