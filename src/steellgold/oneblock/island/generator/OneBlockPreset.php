<?php

namespace steellgold\oneblock\island\generator;

use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\world\ChunkManager;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\SingleOne;

class OneBlockPreset extends IslandGenerator {

	public function getName(): string {
		return "OneBlock";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		if ($chunkX == 0 >> 4 and $chunkZ == -1 >> 4) {
			$world->setBlockAt(0, 40, 0, VanillaBlocks::CHEST());
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		if ($chunkX == 0 >> 4 and $chunkZ == -1 >> 4) {
			$block = $world->getBlockAt(0, 40, 0);
			/** @var Chest $block */
			foreach (SingleOne::getInstance()->getIslandConfig()->get("start_chest_content") as $item) {
				$i = explode(':', $item);
				$block->getInventory()->addItem(ItemFactory::getInstance()->get($i[0], $i[1])->setCount($i[2] ?? 1));
			}
			$world->setBlockAt(0, 40, 0, $block);
		}
	}

	public static function getDefaultBlockPosition(): Vector3 {
		return new Vector3(0, 40, 0);
	}

	public static function getWorldSpawn(): Vector3 {
		return new Vector3(0, 42, 0);
	}
}