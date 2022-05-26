<?php

namespace steellgold\oneblock;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class SingleOne {
	use SingletonTrait;

	public function getIslandConfig() : Config {
		return One::getInstance()->getIslandConfig();
	}
}