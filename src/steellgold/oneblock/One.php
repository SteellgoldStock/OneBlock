<?php

namespace steellgold\oneblock;

use CortexPE\Commando\PacketHooker;
use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\generator\GeneratorManager;
use steellgold\oneblock\commands\IslandCommand;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\island\IslandFactory;
use steellgold\oneblock\listeners\IslandListener;
use steellgold\oneblock\provider\Manager;
use steellgold\oneblock\provider\Text;

class One extends PluginBase {

	public Manager $manager;

	/**
	 * @var One
	 */
	public static One $instance;

	public Config $islandConfig;

	public function getDefaultBlock() {
		$config = $this->getIslandConfig();
		$block = explode(':', $config->get("default-block"));
		return BlockFactory::getInstance()->get($block[0], $block[1]);
	}

	protected function onLoad(): void {
		if (!is_dir($this->getDataFolder() . "islands")) mkdir($this->getDataFolder() . "islands");
		if (!file_exists($this->getDataFolder() . "config.yml")) $this->saveResource("config.yml", true);
		if (!file_exists($this->getDataFolder() . "island.yml")) $this->saveResource("island.yml", true);
		GeneratorManager::getInstance()->addGenerator(OneBlockPreset::class, "OneBlock", fn() => null, true);

		self::$instance = $this;
		$this->islandConfig = new Config($this->getDataFolder() . "island.yml", Config::YAML);
	}

	protected function onEnable(): void {
		$this->manager = new Manager();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->getServer()->getCommandMap()->register("oneblock", new IslandCommand($this, "island", Text::getCommandDescription("default"), ["is"]));
		$this->getServer()->getPluginManager()->registerEvents(new IslandListener(), $this);
	}

	protected function onDisable(): void {
		foreach ($this->manager->getSessions() as $session) {
			$session->closeSession();
		}
	}

	public function getIslandConfig(): Config {
		if (!$this->islandConfig) $this->islandConfig = new Config($this->getDataFolder() . "island.yml", Config::YAML);
		return $this->islandConfig;
	}

	public function getManager(): Manager {
		return $this->manager;
	}

	/**
	 * @return One
	 */
	public static function getInstance(): One {
		return self::$instance;
	}
}