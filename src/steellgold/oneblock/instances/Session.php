<?php

namespace steellgold\oneblock\instances;

use JsonException;
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
	 * @param array $current_invite
	 * @param Player|null $current_invite_player
	 */
	public function __construct(
		private Player  $player,
		private ?Island $island,
		private ?Rank   $rank,
		private bool    $isInIsland = false,
		private bool    $isInVisit = false,
		private array   $current_invite = [],
		private ?Player $current_invite_player = null
	) {

	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function setRank(?Rank $rank): void {
		$this->rank = $rank;
	}

	public function getRank(): ?Rank {
		return $this->rank;
	}

	/**
	 * @throws JsonException
	 */
	public function setIsland(?Island $island): void {
		$file = One::getInstance()->getManager()->player_data;
		if ($island === null) {
			$file->set($this->player->getName(), null);
		} else {
			$file->set($this->player->getName(), $island->getId());
		}
		$file->save();

		$this->island = $island;
	}

	public function getIsland(): Island {
		return $this->island;
	}

	public function isInIsland(): bool {
		return $this->isInIsland;
	}

	public function setIsInIsland(bool $isInIsland): void {
		$this->isInIsland = $isInIsland;
	}

	public function isInVisit(): bool {
		return $this->isInVisit;
	}

	public function setIsInVisit(bool $isInVisit): void {
		$this->isInVisit = $isInVisit;
	}

	public function hasIsland(): bool {
		return $this->island !== null;
	}

	public function closeSession(): void {
		One::getInstance()->getLogger()->info("Closing session for player " . $this->player->getName());
		One::getInstance()->getManager()->close("sessions", $this->player->getName());

		if ($this->island !== null) {
			$members = count(array_keys($this->island->getMembers()));
			$i = 0;
			foreach ($this->island->getMembers() as $member) {
				if (!Server::getInstance()->getPlayerByPrefix($member) instanceof Player) {
					$i++;
				}
			}

			if ($i == $members) {
				$this->island->save();

				Server::getInstance()->getWorldManager()->unloadWorld($this->island->getWorld());
				One::getInstance()->getLogger()->info("Closing island " . $this->island->getId());
				One::getInstance()->getManager()->close("islands", $this->island->getId());
			}
		}
	}

	public function hasInvitation(): bool {
		if ($this->current_invite == []) return false;
		if (time() >= $this->current_invite["time"]) {
			return false;
		}

		return true;
	}

	/**
	 * @return Player|null
	 */
	public function getInviter(): ?Player {
		return $this->current_invite_player;
	}

	public function addInvite(Island $island, Player $inviter): bool {
		if (time() >= $this->current_invite["expire"]) {
			return false;
		}

		$this->current_invite_player = $inviter;
		$this->current_invite = [
			"id" => $island->getId(),
			"expire" => time() + One::getInstance()->getConfig()->get("invite-time")
		];
		return true;
	}

	public function acceptInvitation(): bool {
		if (time() >= $this->current_invite["expire"]) {
			return false;
		}

		return true;
	}

	public function denyInvitation(): bool {
		if (time() >= $this->current_invite["expire"]) {
			return false;
		}

		return true;
	}

	public function removeInviteCache(): void {
		$this->current_invite = [];
		$this->current_invite_player = null;
	}

	/**
	 * @return array
	 */
	public function getCurrentInvite(): array {
		return $this->current_invite;
	}
}