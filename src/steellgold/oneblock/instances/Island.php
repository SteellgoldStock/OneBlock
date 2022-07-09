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
use xenialdan\apibossbar\BossBar;

class Island {

	/**
	 * @param string $id
	 * @param string $owner
	 * @param array $members
	 * @param array $visitors
	 * @param array $spawn
	 * @param Tier $tier
	 * @param int $count
	 * @param int $objective
	 * @param bool $isPublic
	 * @param int $pts
	 * @param array $blocksPoints
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
		public bool   $isPublic,
		public int    $pts,
		public array  $blocksPoints
	) {
		$this->init();
	}

	/**
	 * @param string $owner
	 */
	public function setOwner(string $owner): void {
		$this->owner = $owner;
	}

	/** @var BossBar */
	public static BossBar $bar;

	/**
	 * @throws JsonException
	 */
	public function init(): void {
		if (!file_exists(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json")) {
			$island = new Config(One::getInstance()->getDataFolder() . "islands/" . $this->id . ".json", Config::JSON);
			$island->set("owner", $this->owner);
			$island->set("members", $this->members);
			$island->set("visitors", []);
			$this->extracted($island);
		}

		self::$bar = new BossBar();
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
		$this->extracted($island);
	}

	public function getRank(string $player, bool $integer = false): int|Rank {
		return $integer ? $this->members[$player] : $this->getRankById($this->members[$player] ?? 0);
	}

	public function getRankById(int $id): Rank {
		return One::getInstance()->getManager()->ranks[$id];
	}

	public function setRank(string $player, int $rank, bool $updateSession = true, bool $saveDirectly = true): void {
		$this->members[$player] = $rank;
		if($saveDirectly) {
			$this->save();
		}

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

		return key_exists($name, $this->members);
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

	public function addToObjective(int $count = 1): void {
		$this->count += $count;
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

	public function getCount(): int {
		return $this->count;
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
				$this->sendSuccess($mbr, $this->getTier(), $old);
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

	/**
	 * @throws JsonException
	 */
	public function delete(): void {
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

	public function isMember(string $player): bool {
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

	public function removeBossbarFromAll(): void {
		self::$bar->removeAllPlayers();
	}

	public function haveBossbar(Player $player): bool {
		return in_array($player, self::$bar->getPlayers());
	}

	public function updateBossbar(): void {
		if ($this->isTierMax()) {
			if (count(self::$bar->getPlayers()) >= 1) self::$bar->removeAllPlayers();
			return;
		}

		foreach ($this->getMembers() as $member => $rank) {
			$p = Server::getInstance()->getPlayerByPrefix($member);
			if ($p instanceof Player) {
				if (!str_starts_with($p->getWorld()->getFolderName(), "island-")) {
					$this->removeBossbar($p);
				} else {
					if (!$this->haveBossbar($p)) $this->addBossbar($p);
				}

				self::$bar->removePlayer($p);
				self::$bar->setTitle(str_replace("{OWNER}", $this->getOwner(), One::getInstance()->getConfig()->get("messages")["bb-title"]));
				self::$bar->setSubTitle(str_replace(["{COUNT}", "{TIER_NAME}", "{MAX}"], [$this->getObjective(), $this->getTier()->getName(), $this->getTier()->getBreakToUp()], One::getInstance()->getConfig()->get("messages")["bb-subtitle"]));
				self::$bar->setPercentage($this->getObjective() / $this->getTier()->getBreakToUp());
				self::$bar->addPlayer($p);
			}
		}
	}

	// other

	private function sendSuccess(Player $player, Tier $tier, Tier $oldTier): void {
		$config = One::getInstance()->getIslandConfig()->get("tier_up");

		$find = ["{UPPER}", "{TIER_NAME}", "{TIER_LEVEL}", "{OLD_TIER_NAME}"];
		$replace = [$player->getName(), $tier->getName(), $tier->getId(), $oldTier->getName()];

		switch ($config["type"]) {
			case "title":
				foreach ($this->getMembers() as $member => $rank) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						$player->sendTitle(str_replace($find, $replace, $config["title"]), str_replace($find, $replace, $config["subtitle"]));
					}
				}
				break;
			case "tip":
			case "popup":
				foreach ($this->getMembers() as $member => $rank) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						if ($config["type"] == "tip") $player->sendTip(str_replace($find, $replace, $config["tip"]));
						if ($config["type"] == "popup") $player->sendTip(str_replace($find, $replace, $config["popup"]));
					}
				}
				break;
			case "message":
				foreach ($this->getMembers() as $member => $rank) {
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if ($player instanceof Player) {
						$player->sendMessage(Text::getMessage("tier_up", false, $find, $replace, "message"));
					}
				}
				break;
		}
	}

	# POINTS

	public function addPoints(int $points = 1): void {
		$this->pts += $points;
	}

	public function removePoints(int $points = 1): void {
		$this->pts -= $points;
	}

	public function getPoints(): int {
		return $this->pts;
	}

	public function addBlockPoint(string $xyz, string $data): void {
		$this->blocksPoints[$xyz] = $data;
	}

	public function removeBlockPoint(string $xyz): void {
		unset($this->blocksPoints[$xyz]);
	}

	public function existBlockPoint(string $xyz): bool {
		return key_exists($xyz, $this->blocksPoints);
	}

	/**
	 * @param Config $island
	 * @return void
	 * @throws JsonException
	 */
	public function extracted(Config $island): void {
		$island->set("spawn", $this->spawn);
		$island->set("tier", $this->tier->getId());
		$island->set("count", $this->count);
		$island->set("objective", $this->objective);
		$island->set("isPublic", $this->isPublic);
		$island->set("pts", $this->pts);
		$island->set("blocksPoints", $this->blocksPoints);
		$island->save();
	}

	public function broadcast(string $message): int {
		$i = 0;
		foreach ($this->getMembers() as $member => $rank) {
			$player = One::getInstance()->getServer()->getPlayerExact($member);
			if ($player instanceof Player) {
				$player->sendMessage(One::getInstance()->getConfig()->get("messages")["prefix"]["success"] . $message);
				$i++;
			}
		}
		return $i;
	}
}