<?php

namespace steellgold\oneblock\island;

use JsonException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\One;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\task\ChestPlaceTask;
use steellgold\oneblock\utils\RankIds;

class IslandFactory {

	/**
	 * @throws JsonException
	 */
	public static function createIsland(Player $owner, Tier $tier): void {
		$spawn = One::getInstance()->getIslandConfig()->get("spawn");

		$identifier = uniqid("island-");
		self::createWorld($identifier);

		One::getInstance()->getManager()->addIsland(new Island(
			$identifier,
			$owner->getName(),
			[$owner->getName() => RankIds::LEADER],
			[],
			[
				"X" => $spawn["x"],
				"Y" => $spawn["y"],
				"Z" => $spawn["z"],
			],
			$tier,
			0,
			true
		));

		$owner->sendMessage(Text::getMessage("island_created"));
		$owner->teleport(One::getInstance()->getManager()->getIsland($identifier)->getSpawn(true));
		$owner->sendMessage(Text::getMessage("island_teleported"));

		One::getInstance()->getScheduler()->scheduleDelayedTask(new ChestPlaceTask($owner, $identifier), 20);
	}

	public static function createWorld(string $identifier): World {
		$server = One::getInstance()->getServer()->getWorldManager();
		$server->generateWorld($identifier, (new WorldCreationOptions())->setGeneratorClass(OneBlockPreset::class), false);
		$server->loadWorld($identifier);
		$server->getWorldByName($identifier)->loadChunk(0, 0);
		return $server->getWorldByName($identifier);
	}

	public static function getIsland(World $world): ?Island {
		return One::getInstance()->getManager()->islands[$world->getFolderName()] ?? null;
	}

	/**
	 * @throws JsonException
	 */
	public static function restoreIsland(string $identifier): void {
		if (One::getInstance()->getServer()->getWorldManager()->loadWorld($identifier)) {
			$file = new Config(One::getInstance()->getDataFolder() . "islands/" . $identifier . ".json", Config::JSON);

			One::getInstance()->getManager()->addIsland(new Island(
				$identifier,
				$file->get("owner"),
				$file->get("members"),
				[],
				$file->get("spawn"),
				One::getInstance()->getManager()->getTier($file->get("tier")),
				$file->get("objective"),
				$file->get("isPublic")
			), true);
		}
	}
}