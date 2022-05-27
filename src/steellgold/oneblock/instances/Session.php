<?php

namespace steellgold\oneblock\instances;

use pocketmine\player\Player;
use pocketmine\Server;
use steellgold\oneblock\One;

class Session {

	/**
	 * @param Player $player
	 * @param Island|null $island
	 * @param Rank|null $rank
	 * @param bool $isInIsland
	 * @param bool $isInVisit
	 */
	public function __construct(
		private Player $player,
		private ?Island $island,
		private ?Rank $rank,
		private bool $isInIsland = false,
		private bool $isInVisit = false,
	) {

	}

	public function getPlayer() : Player {
		return $this->player;
	}

	public function setRank(?Rank $rank): void {
		$this->rank = $rank;
	}

	public function getRank(): ?Rank {
		return $this->rank;
	}

	public function setIsland(?Island $island): void {
		$this->island = $island;
	}

	public function getIsland() : Island {
		return $this->island;
	}

	public function isInIsland() : bool {
		return $this->isInIsland;
	}

	public function setIsInIsland(bool $isInIsland): void {
		$this->isInIsland = $isInIsland;
	}

	public function isInVisit() : bool {
		return $this->isInVisit;
	}

	public function setIsInVisit(bool $isInVisit): void {
		$this->isInVisit = $isInVisit;
	}

	public function hasIsland() : bool {
		return $this->island !== null;
	}

	public function closeSession() : void {
		One::getInstance()->getLogger()->info("Closing session for player " . $this->player->getName());
		One::getInstance()->getManager()->close("sessions", $this->player->getName());
		var_dump($this->island);
		if($this->island !== null) {
			$members = count(array_keys($this->island->getMembers()));
			var_dump($members);
			$i = 0;
			foreach ($this->island->getMembers() as $member) {
				if(Server::getInstance()->getPlayerByPrefix($member) instanceof Player) {
					$i++;
				}
			}

			if($i == $members){
				Server::getInstance()->getWorldManager()->unloadWorld($this->island->getWorld());
				One::getInstance()->getLogger()->info("Closing island " . $this->island->getId());
				One::getInstance()->getManager()->close("islands", $this->island->getId());
			}
		}
	}
}