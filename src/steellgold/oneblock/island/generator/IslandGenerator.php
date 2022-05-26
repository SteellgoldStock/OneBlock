<?php

namespace steellgold\oneblock\island\generator;

use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

abstract class IslandGenerator extends Generator {

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		return;
	}

	public abstract static function getWorldSpawn(): Vector3;

	public abstract static function getDefaultBlockPosition(): Vector3;

}