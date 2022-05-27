<?php

namespace steellgold\oneblock\instances;

use JsonException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use steellgold\oneblock\One;
use steellgold\oneblock\utils\RankIds;

class Island {

	/**
	 * @param string $id
	 * @param string $owner
	 * @param Player[] $members
	 * @param array $spawn
	 * @param Tier $tier
	 * @param bool $isPublic
	 * @throws JsonException
	 */
	public function __construct(
		public string $id,
		public string $owner,
		public array  $members,
		public array  $spawn,
		public Tier   $tier,
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
			$island->set("spawn", $this->spawn);
			$island->set("tier", $this->tier);
			$island->set("isPublic", $this->isPublic);
			$island->save();
			return;
		}
	}

	public function getRankById(int $id) {
		return One::getInstance()->getManager()->ranks[$id];
	}

	public function getId(): string {
		return $this->id;
	}

	public function getOwner(): string {
		return $this->owner;
	}

	public function getMembers(): array {
		return $this->members;
	}

	public function getRank(string $player): Rank {
		return $this->getRankById($this->members[$player] ?? 0);
	}

	public function setMembers(array $members): void {
		$this->members = $members;
	}

	public function getSpawn(bool $high = false): Position {
		return new Position($this->spawn["X"], $high ? $this->spawn["Y"] + 100 : $this->spawn["Y"], $this->spawn["Z"], $this->getWorld());
	}

	public function getWorld(): World {
		$wm = One::getInstance()->getServer()->getWorldManager();

		if(!$wm->isWorldLoaded($this->id)) $wm->loadWorld($this->id);
		return One::getInstance()->getServer()->getWorldManager()->getWorldByName($this->id);
	}

	public function setSpawn(array $spawn): void {
		$this->spawn = $spawn;
	}

	public function getTier(): Tier {
		return $this->tier;
	}

	public function addTier(): bool {
		if (!key_exists(($this->tier->getId() + 1), One::getInstance()->tiers)) {
			return false;
		}

		$this->tier = One::getInstance()->tiers[$this->tier->getId() + 1];
		return true;
	}

	public function setTier(Tier $tier): void {
		$this->tier = $tier;
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

	public function setIsPublic(bool $isPublic): void {
		$this->isPublic = $isPublic;
	}
}