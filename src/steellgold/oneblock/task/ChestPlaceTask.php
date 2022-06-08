<?php

namespace steellgold\oneblock\task;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use steellgold\oneblock\One;
use steellgold\oneblock\SingleOne;

class ChestPlaceTask extends Task {

	public function __construct(
		public Player $player,
		public string $identifier,
	) {
	}

	public function onRun(): void {
		One::getInstance()->getServer()->getWorldManager()->loadWorld($this->identifier);
		$world = One::getInstance()->getServer()->getWorldManager()->getWorldByName($this->identifier);

		$chestPosition = SingleOne::getInstance()->getIslandConfig()->get("spawn");
		$world->setBlock(new Position($chestPosition["x"], $chestPosition["y"] - 2, $chestPosition["z"], $world), VanillaBlocks::CHEST());
		$tile = $world->getTile(new Position($chestPosition["x"], $chestPosition["y"] - 2, $chestPosition["z"], $world));
		foreach (One::getInstance()->getIslandConfig()->get("start_inventory") as $items) {
			$item = explode(":", $items);
			$tile->getInventory()->addItem(ItemFactory::getInstance()->get($item[0], $item[1], $item[2] ?? 1));
		}
		$this->player->teleport(One::getInstance()->getManager()->getIsland($this->identifier)->getSpawn());
	}
}