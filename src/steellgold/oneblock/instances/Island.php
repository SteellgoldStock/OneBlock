<?php

namespace steellgold\oneblock\instances;

use JsonException;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class Island {

	/**
	 * @param string $id
	 * @param string $owner
	 * @param string $members
	 * @param array $visitors
	 * @param array $spawn
	 * @param Tier $tier
	 * @param int $objective
	 * @param bool $isPublic
	 * @throws JsonException
	 */
	public function __construct(
		public string $id,
		public string $owner,
		public array  $members,
		public array  $visitors,
		public array  $spawn,
		public Tier   $tier,
		public int    $objective,
		public bool   $isPublic
	) {
		$this->init();
	}

	/**
	 * @throws JsonException
	 */
	public function init(): void {
		if (!file_exists(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json")) {
			$island = new Config(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json", Config::JSON);
			$island->set("owner", $this->owner);
			$island->set("members", $this->members);
			$island->set("visitors", []);
			$island->set("spawn", $this->spawn);
			$island->set("tier", $this->tier->getId());
			$island->set("objective", $this->objective);
			$island->set("isPublic", $this->isPublic);
			$island->save();
		}
	}

	public function getId(): string {
		return $this->id;
	}

	/**
	 * @throws JsonException
	 */
	public function save(): void {
		$island = new Config(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json", Config::JSON);
		$island->set("owner", $this->owner);
		$island->set("members", $this->members);
		$island->set("visitors", $this->visitors);
		$island->set("spawn", $this->spawn);
		$island->set("tier", $this->tier->getId());
		$island->set("objective", $this->objective);
		$island->set("isPublic", $this->isPublic);
		$island->save();
	}

	public function getRank(string $player, bool $integer = false): int|Rank {
		return $integer ? $this->members[$player] : $this->getRankById($this->members[$player] ?? 0);
	}

	public function getRankById(int $id): Rank {
		return One::getInstance()->getManager()->ranks[$id];
	}

	public function setRank(string $player, int $rank, bool $updateSession = true): void {
		$this->members[$player] = $rank;
		$this->save();

		if ($updateSession) {
			$p = Server::getInstance()->getPlayerByPrefix($player);
			if ($p instanceof Player) {
				$session = One::getInstance()->getManager()->getSession($player);
				$session->setRank($this->getRankById($rank));
			}
		}
	}

	public function getOwner(): string {
		return $this->owner;
	}

	public function addMember(Player $player, int $rankId): void {
		$this->members[$player->getName()] = $rankId;
	}

	public function delMember(string $player): void {
		unset($this->members[$player]);
	}

	public function hasMember(string $name): bool {
		if(Server::getInstance()->isOp($name)) {
			return true;
		}

		return isset($this->members[$name]);
	}

	public function addVisitor(string $player): void {
		$this->visitors[] = $player;
	}

	public function getVisitors(): array {
		return $this->visitors;
	}

	public function getSpawn(bool $high = false): Position {
		return new Position($this->spawn["X"], $high ? $this->spawn["Y"] + 100 : $this->spawn["Y"], $this->spawn["Z"], $this->getWorld());
	}

	public function getWorld(): World {
		$wm = One::getInstance()->getServer()->getWorldManager();

		if (!$wm->isWorldLoaded($this->id)) $wm->loadWorld($this->id);
		return One::getInstance()->getServer()->getWorldManager()->getWorldByName($this->id);
	}

	public function setSpawn(array $spawn): void {
		$this->spawn = $spawn;
		$this->getWorld()->setSpawnLocation(new Position($spawn["X"], $spawn["Y"], $spawn["Z"], $this->getWorld()));
	}

	public function isTierMax(): bool {
		if (!key_exists(($this->tier->getId() + 1), One::getInstance()->getManager()->tiers)) {
			return true;
		} else return false;
	}

	public function addToObjective(Player $player, int $count = 1): void {
		$this->objective += $count;
		if ($this->checkTier()) {
			$tier = $this->addTier();
			if ($tier == "max") {
				return;
			}
			$this->sendSuccess($player, One::getInstance()->getManager()->getTier($this->getTier()->getId() - 1));
		}
	}

	public function checkTier(): string|bool {
		if ($this->getObjective() >= $this->getTier()->getBreakToUp()) {
			return true;
		} else {
			return false;
		}
	}

	public function getObjective(): int {
		return $this->objective;
	}

	public function getTier(): Tier {
		return $this->tier;
	}

	public function addTier(): bool|string {
		if (!key_exists(($this->tier->getId() + 1), One::getInstance()->getManager()->tiers)) {
			return "max";
		}

		$this->setTier(One::getInstance()->getManager()->getTier($this->tier->getId() + 1));
		return true;
	}

	public function setTier(Tier $tier): void {
		$this->tier = $tier;
	}

	private function sendSuccess(Player $player, Tier $tier): void {
		$config = One::getInstance()->getIslandConfig()->get("tier_up");

		$find = ["{UPPER}", "TIER_LEVEL}", "{TIER_NAME}"];
		$replace = [$player->getName(), $tier->getId(), $tier->getName()];

		switch ($config["type"]) {
			case "title":
				foreach ($this->getMembers() as $member) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						$player->sendTitle(str_replace($find, $replace, $config["title"]), str_replace($find, $replace, $config["subtitle"]), $config["time"]);
					}
				}
				break;
			case "tip":
			case "popup":
				foreach ($this->getMembers() as $member) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						if ($config["type"] == "tip") $player->sendTip(str_replace($find, $replace, $config["tip"]));
						if ($config["type"] == "popup") $player->sendTip(str_replace($find, $replace, $config["popup"]));
					}
				}
				break;
			case "message":
				foreach ($this->getMembers() as $member) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						$player->sendMessage(Text::getMessage("tier_up", false, $find, $replace, "message"));
					}
				}
				break;
		}
	}

	public function getMembers(): array {
		return $this->members;
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

	public function setIsPublic(bool $isPublic): void {
		$this->isPublic = $isPublic;
	}

	public function delete() {
		$players = One::getInstance()->getManager()->player_data;
		foreach ($this->getMembers() as $member => $rankID) {
			$msess = One::getInstance()->getManager()->getSession($member);
			if ($msess !== null) {
				$msess->setIsland(null);
				$msess->setIsInIsland(false);
				$msess->setIsInVisit(false);
			} else {
				$players->set($member, null);
				$players->save();
			}
		}

		One::getInstance()->getServer()->getWorldManager()->unloadWorld($this->getWorld());
		unlink(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json");
		One::getInstance()->getManager()->close("islands", $this->id);
	}

	public function isMember(string $player) {
		return key_exists($player, $this->members);
	}

	public function getPlayerRank(string $player) {
		return $this->members[$player];
	}
}