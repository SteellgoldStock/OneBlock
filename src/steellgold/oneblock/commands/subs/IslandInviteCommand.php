<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandInviteCommand extends BaseSubCommand {

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

		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("invite")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("invite")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["invite", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		$player = Server::getInstance()->getPlayerByPrefix($args["player"]);
		if (!$player instanceof Player) {
			$sender->sendMessage(Text::getMessage("player_not_found", true, ["{PLAYER}"], [$args["player"]]));
			return;
		}

		$player_session = One::getInstance()->getManager()->getSession($player);
		if(!$player_session->getPlayer()->isOnline()){
			$sender->sendMessage(Text::getMessage("player_not_found", true, ["{PLAYER}"], [$args["player"]]));
			return;
		}

		if ($player_session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("player_island_already", true, ["{PLAYER}"], [$player->getName()]));
			return;
		}

		$invite = $player_session->addInvite($session->getIsland(), $sender);
		var_dump($invite);
		if ($invite) {
			$sender->sendMessage(Text::getMessage("island_invited", false, ["{PLAYER}"], [$player->getName()]));
			if ($player->isOnline()) $player->sendMessage(Text::getMessage("island_invited_by", false, ["{OWNER}"], [$session->getIsland()->getOwner()]));
		} else {
			$sender->sendMessage(Text::getMessage("island_invited_already", true, ["{PLAYER}"], [$player->getName()]));
		}
	}

	protected function prepare(): void {
		$this->registerArgument(0, new TargetArgument("player", false));
	}
}