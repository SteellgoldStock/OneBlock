<?php

namespace steellgold\oneblock\island\generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;

class OneBlockPreset extends IslandGenerator {

	public function getName(): string {
		return "OneBlock";
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		if ($chunkX == 0 >> 4 and $chunkZ == -1 >> 4) {
			$world->setBlockAt(self::getWorldSpawn()->getX(), self::getWorldSpawn()->getY() - 3, self::getWorldSpawn()->getZ(), VanillaBlocks::BEDROCK());
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		return;
	}
}