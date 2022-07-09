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
				$player->sendForm($this->openSearch(match ($selectedOption) {
					0 => "leader",
					1 => "delete",
					2 => "teleport",
					3 => "tiers"
				}));
			}
		);
	}

	public function openSearch(string $option) : CustomForm {
		return new CustomForm(
			"Chercher un propriétaire d'île",
			[
				new Input("start_with","Qui commence par?","Jean")
			],
			function (Player $player, CustomFormResponse $response) use ($option) : void {
				$player->sendForm($this->getList($response->getString("start_with"), $option));
			}
		);
	}

	// create function that list player, characters star with "aaa"
	public function getList(string $startWith, string $option) : MenuForm|CustomForm {
		$files = scandir(One::getInstance()->getDataFolder() . "islands/");
		unset($files[0], $files[1]);
		$files = array_values($files);

		$buttons = [];
		$players = [];
		$i = 0;
		foreach ($files as $fileName) {
			$file = new Config(One::getInstance()->getDataFolder() . "islands/" . $fileName, Config::JSON);
			if (str_starts_with($file->get("owner"), $startWith)) {
				$buttons[] = new MenuOption($file->get("owner"));
				$players[$i] = str_replace(".json", "", $fileName);
				$i++;
			}
		}

		return new MenuForm(
			"Liste des îles",
			"Selectioner une île",
			$buttons,
			function (Player $player, int $selectedOption) use ($players, $option) : void {
				if ($option == "leader") {
					$player->sendForm($this->openEditLeaderForm($players[$selectedOption]));
				}
			}
		);
	}

	public function openEditLeaderForm(string $island) : CustomForm {
		return new CustomForm(
			"Gestion des propriétaires",
			[
				new Label("label",str_replace("{ISLAND_ID}", $island, One::getInstance()->getFormConfig()->get("leader_form")["label"])),
				new Input("new_owner",One::getInstance()->getFormConfig()->get("leader_form")["new_owner"],$player->getName())
			],
			function (Player $player, CustomFormResponse $response): void {
				var_dump($response->getString("label"));
				var_dump($response->getString("new_owner"));
			}
		);
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}