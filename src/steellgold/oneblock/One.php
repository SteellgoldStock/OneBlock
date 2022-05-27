<?php

namespace steellgold\oneblock;

use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\generator\GeneratorManager;
use steellgold\oneblock\commands\IslandCommand;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\listeners\IslandListener;
use steellgold\oneblock\provider\Text;

class One extends PluginBase {

	public array $tiers = [];

	/** @var Island[] $islands */
	public array $islands = [];

	/**
	 * @var One
	 */
	public static One $instance;

	public Config $islandConfig;

	protected function onEnable(): void {
		self::$instance = $this;

		if (!file_exists($this->getDataFolder() . "config.yml")) {
			if(!is_dir($this->getDataFolder() . "islands")) mkdir($this->getDataFolder() . "islands");
			$this->saveResource("config.yml",true);
			$this->saveResource("islandConfig.yml",true);
		}
		$this->islandConfig = new Config($this->getDataFolder() . "islandConfig.yml", Config::YAML);

		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		foreach ($this->islandConfig->get("tiers") as $tierId => $tier){
			$this->tiers[$tierId] = new Tier($tierId, $tier["name"], $tier["breakToUp"], $tier["blocks"]);
		}

		GeneratorManager::getInstance()->addGenerator(OneBlockPreset::class, "OneBlock",fn() => null, true);

		$this->getServer()->getCommandMap()->register("oneblock", new IslandCommand($this, "island", Text::getCommandDescription("default"), ["is"]));
		$this->getServer()->getPluginManager()->registerEvents(new IslandListener(), $this);
	}

	public function getIslandConfig(): Config {
		if (!$this->islandConfig) $this->islandConfig = new Config($this->getDataFolder() . "islandConfig.yml", Config::YAML);
		return $this->islandConfig;
	}

	/**
	 * @return One
	 */
	public static function getInstance() : One {
		return self::$instance;
	}
}