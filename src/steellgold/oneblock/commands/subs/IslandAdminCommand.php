<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\BaseSubCommand;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
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

		if (!Server::getInstance()->isOp($sender->getName()) or !$sender->hasPermission("oneblock.admin")) {
			$sender->sendMessage("§cYou must be an operator/have permission §foneblock.admin §cto use this command.");
			return;
		}

		$sender->sendForm($this->getHomeForm());
	}

	// Home Form
	public function getHomeForm(): MenuForm {
		return new MenuForm(
			One::getInstance()->getFormConfig()->get("home_form")["title"],
			One::getInstance()->getFormConfig()->get("home_form")["label"],
			[
				new MenuOption(One::getInstance()->getFormConfig()->get("home_form")["leader"]),
				new MenuOption(One::getInstance()->getFormConfig()->get("home_form")["delete"]),
				new MenuOption(One::getInstance()->getFormConfig()->get("home_form")["teleport"]),
				new MenuOption(One::getInstance()->getFormConfig()->get("home_form")["tiers"])
			],
			function (Player $player, int $selectedOption): void {
				$player->sendForm($this->openSearch(match ($selectedOption) {
					0 => "leader",
					1 => "delete",
					2 => "teleport",
					3 => "tiers"
				}));
			}
		);
	}

	public function openSearch(string $option): CustomForm {
		return new CustomForm(
			One::getInstance()->getFormConfig()->get("search_form")["title"],
			[
				new Input("start_with", One::getInstance()->getFormConfig()->get("search_form")["start_with"], One::getInstance()->getFormConfig()->get("search_form")["start_with_placeholder"])
			],
			function (Player $player, CustomFormResponse $response) use ($option): void {
				$player->sendForm($this->getList($response->getString("start_with"), $option));
			}
		);
	}

	public function getList(string $startWith, string $option): MenuForm|CustomForm {
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
			One::getInstance()->getFormConfig()->get("search_form")["title_select"],
			str_replace(["{COUNT}", "{START_WITH}"], [count($players), $startWith], One::getInstance()->getFormConfig()->get("search_form")[count($players) >= 1 ? "message_founds" : "message_error"]),
			$buttons,
			function (Player $player, int $selectedOption) use ($players, $option): void {
				match ($selectedOption) {
					0 => $player->sendForm($this->openEditLeaderForm($players[$selectedOption])),
					1 => $player->sendForm($this->openDeleteForm($players[$selectedOption])),
					// 2 => $player->sendForm($this->openTeleportForm($players[$selectedOption])),
					// 3 => $player->sendForm($this->openTiersForm($players[$selectedOption])),
					default => $player->sendMessage("§cAn error occurred.")
				};
			}
		);
	}

	public function openEditLeaderForm(string $islandId): CustomForm {
		$ranks = [];
		$i = 0;
		foreach (One::getInstance()->getManager()->getRanks() as $rank) {
			$ranks[$i] = $rank->getName();
			$i++;
		}

		unset($ranks[0], $ranks[3]);

		$islandInstance = One::getInstance()->getManager()->getIsland($islandId);
		if ($islandInstance == null) {
			$members = "\nLes membres de l'île sont: §a";
			$islandConfigFile = new Config(One::getInstance()->getDataFolder() . "islands/" . $islandId . ".json", Config::JSON);
			foreach ($islandConfigFile->get("members") as $member => $rank) {
				$members .= $member . "§f, §a";
			}
		}else{
			$members = "\nLes membres de l'île sont: §a";
			foreach ($islandInstance->getMembers() as $member => $rank) {
				$members .= $member . "§f, §a";
			}
		}

		return new CustomForm(
			One::getInstance()->getFormConfig()->get("leader_form")["title"],
			[
				new Label("label", str_replace("{ISLAND_ID}", $islandId, One::getInstance()->getFormConfig()->get("leader_form")["label"]) . $members),
				new Input("new_owner", One::getInstance()->getFormConfig()->get("leader_form")["new_owner"]),
				new Dropdown("new_rank", One::getInstance()->getFormConfig()->get("leader_form")["new_rank"], array_values($ranks), 0)
			],
			function (Player $player, CustomFormResponse $response) use ($islandId, $islandConfigFile): void {
				if (!key_exists($response->getString("new_owner"), $islandConfigFile->get("members"))) {
					$player->sendMessage(One::getInstance()->getConfig()->get("messages")["prefix"]["error"] . "§cCe joueur n'est pas membre de l'île.");
					return;
				}

				$island = One::getInstance()->getManager()->getIsland($islandId);
				if ($island == null) {
					var_dump("aaa");
					$members = [];
					foreach ($islandConfigFile->get("members") as $member => $rank) {
						$members[$member] = $rank;
					}

					$members[$response->getString("new_owner")] = 3;
					$members[$islandConfigFile->get("owner")] = $response->getInt("new_rank");
					$islandConfigFile->set("owner", $response->getString("new_owner"));

					$islandConfigFile->set("members", $members);
					$islandConfigFile->save();
				} else {
					$oldOwnner = $island->getOwner();
					$island->setOwner($response->getString("new_owner"));
					$island->setRank($oldOwnner, match ($response->getInt("new_rank")) {
						0 => 1,
						1 => 2
					}, true, false);
					$island->setRank($response->getString("new_owner"), 3);

					$player->sendMessage(str_replace(["{COUNT_PING}", "{ISLAND_ID}", "{NEW_OWNER}"],
							[
								$island->broadcast(
									str_replace(
										["{NEW_OWNER}", "{OLD_OWNER}"],
										[$response->getString("new_owner"), $oldOwnner],
										One::getInstance()->getFormConfig()->get("leader_form")["message_new_owner"]
									)
								),
								$islandId,
								$response->getString("new_owner")
							],
							One::getInstance()->getFormConfig()->get("leader_form")["message_success"]
						)
					);
				}
			}
		);
	}

	protected function prepare(): void {
		// TODO: Implement prepare() method.
	}
}