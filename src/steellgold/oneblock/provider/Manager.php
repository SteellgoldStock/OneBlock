<?php

namespace steellgold\oneblock\provider;

use JsonException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Rank;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\One;
use Webmozart\PathUtil\Path;

class Manager {

	public array $islands = [];

	public array $sessions = [];

	public array $ranks = [];

	public Config $player_data;

	/**
	 * @throws JsonException
	 */
	public function __construct() {
		foreach (scandir(One::getInstance()->getDataFolder() . "../../worlds/") as $world) {
			if (str_starts_with($world, "island-")) {
				$config = new Config(One::getInstance()->getDataFolder() . "islands/" . $world . ".json", Config::JSON);
				$this->islands[$world] = new Island(
					$world,
					$config->get("owner"),
					$config->get("members"),
					$config->get("spawn"),
					Tier::fromArray($config->get("tier")),
					$config->get("isPublic")
				);
			}
		}

		$i = 0;
		foreach (One::getInstance()->getIslandConfig()->get("ranks") as $rank) {
			$this->ranks[$i] = new Rank($rank["name"], $rank["permissions"], $rank["permissions"] == "*");
			$i++;
		}
		$this->player_data = new Config(One::getInstance()->getDataFolder() . "players.yml", Config::YAML);
	}

	/** @return Island[] */
	public function getIslands(): array {
		return $this->islands;
	}

	public function getIsland(string $identifier): ?Island {
		return $this->islands[$identifier] ?? null;
	}

	public function addIsland(Island $island): void {
		$this->islands[$island->getId()] = $island;
		$this->setIslandToPlayer($island->getOwner(), $island);
	}

	public function setIslandToPlayer(string $player, Island $island): void {
		$this->getSession($player)->setIsland($island);

		$this->player_data->set($player, $island->getId());
		$this->player_data->save();
	}

	public function islandExist(string $identifier): bool {
		return array_key_exists($identifier,$this->islands);
	}

	public function hasIsland(string $player): bool {
		return $this->islandExist($this->player_data->get($player));
	}

	public function getIslandIdentifierByPlayer(string $player): string {
		if($this->islandExist($this->player_data->get($player))){
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
		// If you forget the ->getName() automatically returns the name of the player
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
}