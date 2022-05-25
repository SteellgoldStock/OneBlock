<?php

namespace steellgold\oneblock\instances;

use pocketmine\player\Player;
use pocketmine\world\Position;

class Island {

	/**
	 * @param string $id
	 * @param Player $owner
	 * @param Player[] $members
	 * @param Position $spawn
	 * @param Tier $tier
	 */
	public function __construct(
		public string   $id,
		public Player   $owner,
		public array    $members,
		public Position $spawn,
		public Tier     $tier,
	) { }

	public function getId() : string {
		return $this->id;
	}

	public function getOwner() : Player {
		return $this->owner;
	}

	public function getMembers() : array {
		return $this->members;
	}

	public function getSpawn() : Position {
		return $this->spawn;
	}

	public function getTier() : Tier {
		return $this->tier;
	}
}