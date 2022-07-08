<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use steellgold\oneblock\One;

class IslandAdminCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cPlease run this command in-game.");
			return;
		}

		if (!Server::getInstance()->isOp($sender->getName())) {
			$sender->sendMessage("§cYou must be an operator to use this command.");
			return;
		}

		$sender->sendForm($this->getHomeForm());
	}

	// Home Form
	public function getHomeForm() : MenuForm {
		return new MenuForm(
			"Interaction d'§c§lOP",
			"Administrez les îles",
			[
				new MenuOption("Changer de compte"),
				new MenuOption("Supprimer une île"),
				new MenuOption("Se téléporter à une île"),
				new MenuOption("Gestion des niveaux")
			],
			function (Player $player, int $selectedOption) : void {
				if ($selectedOption == 0) {
					$player->sendForm($this->openList("leader"));
				}
			}
		);
	}

	public function openList(string $option) : MenuForm {

		$files = scandir(One::getInstance()->getDataFolder() . "islands/");
		unset($files[0], $files[1]);
		$files = array_values($files);

		$buttons = [];
		$islands = [];
		$i = 0;
		foreach ($files as $file) {
			$name = str_replace(".json", "", $file);
			$file = new Config(One::getInstance()->getDataFolder() . "islands/" . $file, Config::JSON);
			$islands[$i] = $name;
			$buttons[] = new MenuOption($file->get("owner"));
			$i++;
		}

		return new MenuForm(
			"Iles",
			"Choisissez une île",
			$buttons,
			function (Player $player, int $selectedOption) use ($option, $islands) : void {
				switch ($option) {
					case "leader":
						var_dump($islands[$selectedOption]);
						$player->sendForm($this->openEditLeaderForm($player, $islands[$selectedOption]));
						break;
					case "delete":
						break;
					case "teleport":
						break;
					case "levels":
						break;
				}
			}
		);
	}

	public function openEditLeaderForm(Player $player, string $island) : CustomForm {
		return new CustomForm(
			"Gestion des propriétaires",
			[
				new Label("label","§cSi§f l'ile §c" . $island . " §fest chargée, une fois les modifications appliquées, les membres seront déconnectés, et l'île sera déchargée."),
				new Input("new_leader","Nouveau propriétaire",$player->getName())
			],
			function (Player $player, CustomFormResponse $response): void {
				var_dump($response["label"]);
				var_dump($response["new_leader"]);
			}
		);
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}