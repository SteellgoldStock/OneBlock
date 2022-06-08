<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandInfoCommand extends BaseSubCommand {

	protected function prepare(): void {
		$this->registerArgument(0, new TextArgument("owner", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		if (!isset($args["owner"])) {
			$sender->sendMessage("§c/island info <owner>");
			return;
		}

		$c = count(One::getInstance()->getManager()->player_data->getAll());
		$i = 0;
		foreach (One::getInstance()->getManager()->player_data->getAll() as $player => $island) {
			if ($player == $args["owner"]) {
				if ($island !== null) {
					if (file_exists(One::getInstance()->getDataFolder() . "islands/$island.json")) {
						$island = new Config(One::getInstance()->getDataFolder() . "islands/$island.json", Config::JSON);
						$line = str_replace(["{OWNER}"], [$player], One::getInstance()->getConfig()->get("messages")["island-top"]);
						$line .= "\n" . str_replace(["{TIER_LEVEL}", "{BLOCKS_BREAKED}"], [$island->get("tier"), $island->get("objective")], One::getInstance()->getConfig()->get("messages")["island-line"]);
						$line .= "\n" . str_replace(["{MEMBERS}"], [count($island->get("members"))], One::getInstance()->getConfig()->get("messages")["island-line2"]);
						$sender->sendMessage($line);
					} else {
						$sender->sendMessage(Text::getMessage("player_island_not_exist", true, ["{PLAYER}"], [$player]));
						return;
					}
				} else {
					$sender->sendMessage(Text::getMessage("player_island_not_exist", true, ["{PLAYER}"], [$player]));
					return;
				}
				return;
			} else {
				$i++;
			}
		}

		if ($c == $i) {
			$sender->sendMessage(Text::getMessage("player_not_found", true, ["{PLAYER}"], [$args["owner"]]));
		}
	}
}