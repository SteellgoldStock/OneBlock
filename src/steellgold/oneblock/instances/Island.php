<?php

namespace steellgold\oneblock\instances;

use pocketmine\player\Player;
use pocketmine\world\Position;

class Island {

	/**
	 * @param Player $owner
	 * @param Player[] $members
	 * @param Position $spawn
	 * @param Tier $tier
	 */
	public function __construct(
		public Player $owner,
		public array $members,
		public Position $spawn,
		public Tier $tier,
	) {

	}
}