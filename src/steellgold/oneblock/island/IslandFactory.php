<?php

namespace steellgold\oneblock\island;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;

class IslandFactory {

	public static function createIsland(Player $owner, Tier $tier) {
		$identifier = uniqid("island-");
		One::getInstance()->islands[$identifier] = new Island(
			$identifier,
			$owner,
			[$owner->getName()],
			new Position(0,42,0, self::createWorld($identifier)),
			$tier,
			true
		);
		$owner->teleport(One::getInstance()->islands[$identifier]->getSpawn());
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