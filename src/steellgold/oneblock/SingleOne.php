<?php

namespace steellgold\oneblock;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;

class SingleOne {
	use SingletonTrait;

	private Config $config;

	public function __construct() {
		self::setInstance($this);
		$this->config = new Config(Path::join(getcwd(), "plugin_data", "OneBlock", "islandConfig.yml"), Config::YAML);
	}

	public function getIslandConfig() : Config {
		return $this->config;
	}
}