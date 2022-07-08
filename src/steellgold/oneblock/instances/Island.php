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
		public int    $count,
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
		if (Server::getInstance()->isOp($name)) {
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

		$old = $this->tier;
		$this->objective = 0;
		$this->setTier(One::getInstance()->getManager()->getTier($this->tier->getId() + 1));
		foreach ($this->getMembers() as $member => $rank) {
			$mbr = Server::getInstance()->getPlayerByPrefix($member);
			if ($mbr instanceof Player) {
				$mbr->sendMessage(Text::getMessage("level-passed", false, ["{OLD_TIER_NAME}", "{NEW_TIER_NAME}"], [$old->getName(), $this->getTier()->getName()]));
			}
		}
		return true;
	}

	public function setTier(Tier $tier): void {
		$this->tier = $tier;
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

	// BOSSBAR

	public function addBossbar(Player $player): void {
		self::$bar->addPlayer($player);
	}

	public function removeBossbar(Player $player): void {
		self::$bar->removePlayer($player);
	}

	public function haveBossbar(Player $player): bool {
		return in_array($player, self::$bar->getPlayers());
	}

	public function updateBossbar(): void {
		if($this->isTierMax()) {
			if(count(self::$bar->getPlayers()) >= 1) self::$bar->removeAllPlayers();
			return;
		}

		foreach ($this->getMembers() as $member => $rank) {
			$p = Server::getInstance()->getPlayerByPrefix($member);
			if ($p instanceof Player) {
				if (!str_starts_with($p->getWorld()->getFolderName(), "island-")) {
					$this->removeBossbar($p);
				}else{
					if(!$this->haveBossbar($p)){
						$this->addBossbar($p);
					}
				}
			}

			self::$bar->setTitle(str_replace("{OWNER}", $this->getOwner(), One::getInstance()->getConfig()->get("messages")["bb-title"]));
			self::$bar->setSubTitle(str_replace(["{COUNT}", "{TIER_NAME}", "{MAX}"], [$this->getObjective(), $this->getTier()->getName(), $this->getTier()->getBreakToUp()], One::getInstance()->getConfig()->get("messages")["bb-subtitle"]));
			self::$bar->setPercentage($this->getObjective() / $this->getTier()->getBreakToUp());
		}
	}
}