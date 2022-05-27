<?php

namespace steellgold\oneblock\instances;

use pocketmine\player\Player;
use steellgold\oneblock\One;

class Session {
	public function __construct(
		public Player $player,
		public Island $island,
		public bool $isInIsland = false,
		public bool $isInVisit = false,
	) {

	}

	public function closeSession() : void {
		One::getInstance()->getLogger()->info("Closing session for player " . $this->player->getName());
		unset(One::getInstance()->sessions[$this->player->getName()]);
	}
}