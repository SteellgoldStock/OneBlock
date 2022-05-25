<?php

namespace steellgold\oneblock;

use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use steellgold\oneblock\commands\IslandCommand;
use steellgold\oneblock\instances\Tier;
use steellgold\oneblock\provider\Text;

class One extends PluginBase {

	public array $tiers = [];

	public static $instance;

	protected function onEnable(): void {
		self::$instance = $this;

		if (!file_exists($this->getDataFolder() . "config.yml")) {
			$this->saveResource("config.yml");
			$this->saveResource("islandConfig.yml");
		}

		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$islandConfig = new Config($this->getDataFolder() . "islandConfig.yml", Config::YAML);
		foreach ($islandConfig->get("tiers") as $tierId => $tier){
			$this->tiers[$tierId] = new Tier($tier["name"], $tier["breakToUp"], $tier["blocks"]);
		}

		$this->getServer()->getCommandMap()->register("oneblock", new IslandCommand($this, "island", Text::getCommandDescription("default"), ["is"]));
	}

	/**
	 * @return mixed
	 */
	public static function getInstance() : One {
		return self::$instance;
	}
}