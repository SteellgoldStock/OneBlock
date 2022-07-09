<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandDeleteCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$session = One::getInstance()->getManager()->getSession($sender->getName());
		if (!$session->hasIsland()) {
			$sender->sendMessage(Text::getMessage("dont_have_island", true));
			return;
		}

		if (!$session->getIsland()->getRank($sender->getName())->hasPermission("delete")) {
			foreach (One::getInstance()->getManager()->getRanks() as $rankId => $rank) {
				if ($rank->hasPermission("delete")) {
					$rank_name = $rank->getName();
					$sender->sendMessage(Text::getMessage("no_permission", true, ["{PERMISSION}", "{RANK_HAVE}", "{RANK_TO}"], ["delete", $session->getRank()->getName(), $rank_name]));
					return;
				}
			}
			return;
		}

		$sender->sendForm(self::getForm());
	}

	public static function getForm(): MenuForm {
		$config = One::getInstance()->getConfig()->get("messages");
		return new MenuForm(
			$config["island_delete_form_title"],
			$config["island_delete_form_text"],
			[
				new MenuOption($config["island_delete_form_button_yes"]),
				new MenuOption($config["island_delete_form_button_no"])
			],
			function (Player $player, int $selectedOption): void {
				$session = One::getInstance()->getManager()->getSession($player->getName());
				if ($session == null) return;
				if ($selectedOption == 0) {
					$session->getIsland()->delete();
					$session->setTimer();
					$player->sendMessage(Text::getMessage("island_deleted"));
				}
			}
		);
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}