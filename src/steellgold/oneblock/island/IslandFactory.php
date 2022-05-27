<?php

namespace steellgold\oneblock\island;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\SingleOne;

class IslandFactory {

	public static function createIsland(Player $owner, Tier $tier): void {
		$spawn = One::getInstance()->getIslandConfig()->get("spawn");

		$identifier = uniqid("island-");
		One::getInstance()->islands[$identifier] = new Island(
			$identifier,
			$owner,
			[$owner->getName()],
			new Position($spawn["x"], $spawn["y"], $spawn["z"], self::createWorld($identifier)),
			$tier,
			true
		);

		$owner->sendMessage(Text::getMessage("island_created"));

		$owner->teleport(One::getInstance()->islands[$identifier]->getHighSpawn());
		$owner->sendMessage(Text::getMessage("island_teleported"));

		One::getInstance()->getScheduler()->scheduleDelayedTask(new class($owner, $identifier) extends Task{
			public function __construct(
				public Player $player,
				public string $identifier,
			) {}

			public function onRun() : void{
				One::getInstance()->getServer()->getWorldManager()->loadWorld($this->identifier);
				$world = One::getInstance()->getServer()->getWorldManager()->getWorldByName($this->identifier);

				$chestPosition = SingleOne::getInstance()->getIslandConfig()->get("spawn");
				$world->setBlock(new Position($chestPosition["x"], $chestPosition["y"] - 2, $chestPosition["z"], $world), VanillaBlocks::CHEST());
				$tile = $world->getTile(new Position($chestPosition["x"], $chestPosition["y"] - 2, $chestPosition["z"], $world));
				foreach(One::getInstance()->getIslandConfig()->get("start_inventory") as $items){
					$item = explode(":", $items);
					$tile->getInventory()->addItem(ItemFactory::getInstance()->get($item[0], $item[1], $item[2] ?? 1));
				}
				$this->player->teleport(One::getInstance()->islands[$this->identifier]->getSpawn());
			}
		},20);
	}

	public static function createWorld(string $identifier) : World {
		$server = One::getInstance()->getServer()->getWorldManager();
		$server->generateWorld($identifier,(new WorldCreationOptions())->setGeneratorClass(OneBlockPreset::class),false);
		$server->loadWorld($identifier);
		$server->getWorldByName($identifier)->loadChunk(0,0);
		return $server->getWorldByName($identifier);
	}

	public static function getIsland(World $world) : ?Island {
		return One::getInstance()->islands[$world->getFolderName()] ?? null;
	}
}