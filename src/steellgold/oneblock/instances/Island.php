<?php

namespace steellgold\oneblock\instances;

use JsonException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use steellgold\oneblock\One;

class Island {

	/**
	 * @param string $id
	 * @param Player $owner
	 * @param Player[] $members
	 * @param Position $spawn
	 * @param Tier $tier
	 * @param bool $isPublic
	 */
	public function __construct(
		public string   $id,
		public Player   $owner,
		public array    $members,
		public Position $spawn,
		public Tier     $tier,
		public bool     $isPublic
	) {
		$this->init();
	}

	/**
	 * @throws JsonException
	 */
	public function init(): void {
		if (!file_exists(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json")) {
			$island = new Config(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json", Config::JSON);
			$island->set("owner", $this->owner->getName());
			$island->set("members", json_encode($this->members));
			$island->set("spawn", json_encode($this->spawn));
			$island->set("tier", json_encode($this->tier));
			$island->set("isPublic", $this->isPublic);
			$island->save();
			return;
		}
	}

	public static function fromStdClass(\stdClass $class): Island {
		return new Island(
			$class->id,
			One::getInstance()->getServer()->getPlayerExact($class->owner),
			array_map(function(string $playerName){
				return $playerName;
			}, json_decode($class->members)),
			Position::fromObject(json_decode($class->spawn), One::getInstance()->getServer()->getWorldManager()->getWorldByName($class->spawn->levelName)),
			Tier::fromStdClass(json_decode($class->tier)),
			$class->isPublic
		);
	}

	public function getId(): string {
		return $this->id;
	}

	public function getOwner(): Player {
		return $this->owner;
	}

	public function getMembers(): array {
		return $this->members;
	}

	public function setMembers(array $members): void {
		$this->members = $members;
	}

	public function getSpawn(): Position {
		return $this->spawn;
	}

	public function getHighSpawn(): Position {
		return new Position($this->spawn->x, $this->spawn->y + 100, $this->spawn->z, $this->spawn->world);
	}

	public function setSpawn(Position $spawn): void {
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