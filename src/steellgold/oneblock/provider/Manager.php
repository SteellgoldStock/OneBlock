<?php

namespace steellgold\oneblock\provider;

use JsonException;
use pocketmine\block\BlockFactory;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Rank;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\One;

class Manager {

	/** @var Island[] */
	public array $islands = [];

	/** @var Session[] */
	public array $sessions = [];

	/** @var Tier[] */
	public array $tiers = [];

	/** @var Rank[] */
	public array $ranks = [];

	public Config $player_data;

	/**
	 * @throws JsonException
	 */
	public function __construct() {
		foreach (One::getInstance()->getIslandConfig()->get("tiers") as $tierId => $tier) {
			$blocks = [];
			$i = 0;
			foreach ($tier['blocks'] as $block) {
				$b = explode(':', $block);
				$blocks[$i] = [
					BlockFactory::getInstance()->get($b[0], $b[1]),
					"chance" => $b[2]
				];
				$i++;
			}
			$this->tiers[$tierId] = new Tier($tierId, $tier["name"], $tier["breakToUp"], $blocks);
		}

		foreach (scandir(One::getInstance()->getDataFolder() . "../../worlds/") as $world) {
			if (str_starts_with($world, "island-")) {
				if(file_exists(One::getInstance()->getDataFolder() . "islands/$world.json")){
					$config = new Config(One::getInstance()->getDataFolder() . "islands/$world.json", Config::JSON);
					$this->islands[$world] = new Island(
						$world,
						$config->get("owner"),
						$config->get("members"),
						[],
						$config->get("spawn"),
						$this->getTier($config->get("tier")),
						$config->get("objective"),
						$config->get("isPublic")
					);
				}
			}
		}

		$i = 0;
		foreach (One::getInstance()->getIslandConfig()->get("ranks") as $rank) {
			$this->ranks[$i] = new Rank($rank["name"], $rank["permissions"], $rank["permissions"] == "*");
			$i++;
		}
		$this->player_data = new Config(One::getInstance()->getDataFolder() . "players.yml", Config::YAML);
	}

	public static function generateRandomString($length = 10): string {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	/** @return Island[] */
	public function getIslands(): array {
		return $this->islands;
	}

	public function getIslandsTop(): array {
		$islands = [];
		foreach ($this->islands as $key => $island) {
			$islands[$island->getId()] = $island->getObjective();
		}
		return $islands;
	}

	public function getIsland(string $identifier): ?Island {
		return $this->islands[$identifier] ?? null;
	}

	public function addIsland(Island $island, bool $isReconnect = false): void {
		$this->islands[$island->getId()] = $island;
		if (!$isReconnect) $this->setIslandToPlayer($island->getOwner(), $island);
	}

	public function setIslandToPlayer(string $player, Island $island): void {
		$this->getSession($player)->setIsland($island);

		$this->player_data->set($player, $island->getId());
		$this->player_data->save();
	}

	public function islandFileExist(string $identifier): bool {
		return file_exists(One::getInstance()->getDataFolder() . "islands/" . $identifier . ".json");
	}

	public function hasIsland(string $player): bool {
		return $this->islandFileExist($this->player_data->get($player));
	}

	public function getIslandIdentifierByPlayer(string $player): string {
		if ($this->islandFileExist($this->player_data->get($player))) {
			return $this->player_data->get($player);
		}
		return "";
	}

	public function getIslandByPlayer(string $player): ?string {
		return $this->player_data->get($player);
	}

	/** @return Session[] */
	public function getSessions(): array {
		return $this->sessions;
	}

	public function getSession(Player|CommandSender|string $player): ?Session {
		// If forget the ->getName(), automatically returns the name of the player
		if ($player instanceof Player or $player instanceof CommandSender) {
			$player = $player->getName();
		}
		return $this->sessions[$player] ?? null;
	}

	public function addSession(Session $session): void {
		$this->sessions[$session->getPlayer()->getName()] = $session;
	}

	public function hasSession(string $player): bool {
		return isset($this->sessions[$player]);
	}

	public function close(string $type, string $identifier) {
		unset($this->$type[$identifier]);
	}

	public function getTiers(): array {
		return $this->tiers;
	}

	public function getTier(int $id = 1): Tier {
		return $this->tiers[$id];
	}

	/**
	 * @return Rank[]
	 */
	public function getRanks(): array {
		return $this->ranks;
	}
}