<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandMemberCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		$lines = "";
		$lines .= One::getInstance()->getConfig()->get("messages")["members-top"];
		foreach ($session->getIsland()->getMembers() as $member => $rankID) {
			$lines .= "\n" . str_replace(["{PLAYER}", "{RANK}"], [$member, $session->getIsland()->getRankById($rankID)->getName()], One::getInstance()->getConfig()->get("messages")["members-line"]);
		}
		$sender->sendMessage($lines);
	}

	protected function prepare(): void {

	}
}