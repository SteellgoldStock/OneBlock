<?php

namespace steellgold\oneblock\instances;

use JsonException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\utils\RankIds;

class Island {

	/**
	 * @param string $id
	 * @param string $owner
	 * @param Player[] $members
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
			$island->set("tier", json_encode($this->tier));
			$island->set("objective", $this->objective);
			$island->set("isPublic", $this->isPublic);
			$island->save();
			return;
		}
	}

	public function getRankById(int $id): Rank {
		return One::getInstance()->getManager()->ranks[$id];
	}

	public function getRank(string $player): Rank {
		return $this->getRankById($this->members[$player] ?? 0);
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

	public function setMembers(array $members): void {
		$this->members = $members;
	}

	public function setVisitors(array $visitors): void {
		$this->visitors = $visitors;
	}

	public function addVisitor(string $player): void {
		$this->visitors[] = $player;
	}

	public function delVisitor(string $player): void {
		$this->visitors = array_diff($this->visitors, [$player]);
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

	public function checkTier(): string|bool {
		if($this->getObjective() >= $this->getTier()->getBreakToUp()){
			return true;
		}else{
			return false;
		}
	}

	public function setTier(Tier $tier): void {
		$this->tier = $tier;
	}

	public function getObjective(): int {
		return $this->objective;
	}

	public function addToObjective(Player $player, int $count = 1): void {
		$this->objective += $count;
		var_dump($this->objective . "/" . $this->getTier()->getBreakToUp());
		if($this->checkTier()){
			$tier = $this->addTier();
			var_dump($tier);
			if($tier == "max"){
				return;
			}
			$this->sendSuccess($player,One::getInstance()->getManager()->getTier($this->getTier()->getId() - 1));
		}
	}

	private function sendSuccess(Player $player, Tier $tier): void {
		$config = One::getInstance()->getIslandConfig()->get("tier_up");
		var_dump(1);

		$find = ["{UPPER}","TIER_LEVEL}","{TIER_NAME}"];
		$replace = [$player->getName(),$tier->getId(),$tier->getName()];

		switch ($config["type"]){
			case "title":
				foreach ($this->getMembers() as $member){
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if($player instanceof Player){
						$player->sendTitle(str_replace($find,$replace,$config["title"]), str_replace($find,$replace,$config["subtitle"]), $config["time"]);
					}
				}
				break;
			case "tip":
			case "popup":
				foreach ($this->getMembers() as $member){
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if($player instanceof Player){
						if($config["type"] == "tip") $player->sendTip(str_replace($find,$replace,$config["tip"]));
						if($config["type"] == "popup") $player->sendTip(str_replace($find,$replace,$config["popup"]));
					}
				}
				break;
			case "message":
				foreach ($this->getMembers() as $member){
					$player = One::getInstance()->getServer()->getPlayerExact($member);
					if($player instanceof Player){
						$player->sendMessage(Text::getMessage("tier_up",false,$find,$replace,"message"));
					}
				}
				break;
		}
	}

	public function isPublic(): bool {
		return $this->isPublic;
	}

	public function setIsPublic(bool $isPublic): void {
		$this->isPublic = $isPublic;
	}

	public function save() {
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
}