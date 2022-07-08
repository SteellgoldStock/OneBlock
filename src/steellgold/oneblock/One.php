<?php

namespace steellgold\oneblock;

use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\generator\GeneratorManager;
use steellgold\oneblock\commands\IslandCommand;
use steellgold\oneblock\island\generator\OneBlockPreset;
use steellgold\oneblock\listeners\IslandListener;
use steellgold\oneblock\provider\Manager;
use steellgold\oneblock\provider\Text;
use steellgold\oneblock\task\BossBarTask;
use xenialdan\apibossbar\PacketListener;

class One extends PluginBase {

	/**
	 * @var One
	 */
	public static One $instance;

	public Manager $manager;

	public Config $islandConfig;

	public function getIslandConfig(): Config {
		if (!$this->islandConfig) $this->islandConfig = new Config($this->getDataFolder() . "island.yml", Config::YAML);
		return $this->islandConfig;
	}

	public function getManager(): Manager {
		return $this->manager;
	}

	protected function onLoad(): void {
		if (!is_dir($this->getDataFolder() . "islands")) mkdir($this->getDataFolder() . "islands");
		if (!file_exists($this->getDataFolder() . "config.yml")) $this->saveResource("config.yml", true);
		if (!file_exists($this->getDataFolder() . "island.yml")) $this->saveResource("island.yml", true);
		GeneratorManager::getInstance()->addGenerator(OneBlockPreset::class, "OneBlock", fn() => null, true);

		self::$instance = $this;
		$this->islandConfig = new Config($this->getDataFolder() . "island.yml", Config::YAML);
	}

	/**
	 * @return One
	 */
	public static function getInstance(): One {
		return self::$instance;
	}

	protected function onEnable(): void {
		PacketListener::register();
		$this->manager = new Manager();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->getServer()->getCommandMap()->register("oneblock", new IslandCommand($this, "island", Text::getCommandDescription("default"), ["is"]));
		$this->getServer()->getPluginManager()->registerEvents(new IslandListener(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new BossBarTask(), 20*3);
	}

	protected function onDisable(): void {
		foreach ($this->manager->getSessions() as $session) {
			$session->closeSession();
		}
	}
}