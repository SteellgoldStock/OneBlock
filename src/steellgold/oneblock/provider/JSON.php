<?php

namespace steellgold\oneblock\provider;

use pocketmine\utils\Config;
use steellgold\oneblock\instances\Island;
use steellgold\oneblock\One;

class JSON {

	public function __construct() {
		if(!is_dir(One::getInstance()->getDataFolder() . "islands")){
			mkdir(One::getInstance()->getDataFolder() . "islands");
		}
	}

	public function getIslandConfig(Island $island) : Config {
		return new Config(One::getInstance()->getDataFolder() . $island->getId(), Config::JSON);
	}

	public function createIslandConfig(Island $island) : void {
		$config = new Config(One::getInstance()->getDataFolder() . $island->getId(), Config::JSON);
		$config->set("owner", $island->getOwner()->getName());
		$config->set("members", $island->getMembers());
		$config->set("spawn", $island->getSpawn());
		$config->set("tier", $island->getTier());
		$config->save();
	}

}