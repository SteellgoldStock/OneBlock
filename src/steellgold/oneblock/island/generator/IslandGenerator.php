<?php

namespace steellgold\oneblock\island\generator;

use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use steellgold\oneblock\SingleOne;

abstract class IslandGenerator extends Generator {

	public static function getWorldSpawn(): Vector3 {
		$spawn = SingleOne::getInstance()->getIslandConfig()->get("spawn");
		return new Vector3($spawn["x"], $spawn["y"], $spawn["z"]);
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		return;
	}
}