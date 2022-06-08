<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandKickCommand extends BaseSubCommand {

	protected function prepare(): void {
		$this->registerArgument(0, new TargetArgument("player", false));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender);
		if (!$session->getRank()->hasPermission("kick")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("kick")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["kick", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if ($sender->getName() === $args["player"]) {
			$sender->sendMessage(Text::getMessage("cant_kick_yourself", true));
			return;
		}


		if (!$session->getIsland()->isMember($args["player"])) {
			$sender->sendMessage(Text::getMessage("player_not_member", true, ["{PLAYER}"], [$args["player"]]));
			return;
		}

		if ($session->getIsland()->getPlayerRank($args["player"]) >= $session->getIsland()->getPlayerRank($sender->getName())) {
			$sender->sendMessage(Text::getMessage("kick_failed", true, ["{PLAYER}"], [$args["player"]]));
			return;
		}

		$session->getIsland()->delMember($args["player"]);
		$sender->sendMessage(Text::getMessage("island_kick_success", false, ["{PLAYER}"], [$args["player"]]));
		$p = Server::getInstance()->getPlayerByPrefix($args["player"]);
		if ($p instanceof Player) {
			$p_session = One::getInstance()->getManager()->getSession($p);
			$p_session->setIsland(null);
			if ($p_session->isInIsland()) {
				$p_session->setIsInIsland(false);

				$p->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
			}

			$p->sendMessage(Text::getMessage("island_kicked", false, ["{OWNER}"], [$session->getIsland()->getOwner()]));
		} else {
			$file = One::getInstance()->getManager()->player_data;
			$file->set($args["player"], null);
			$file->save();
		}
	}
}