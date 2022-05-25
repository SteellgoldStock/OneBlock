<?php

namespace steellgold\oneblock;

use pocketmine\plugin\PluginBase;

class One extends PluginBase {

	public static $instance;

	protected function onEnable(): void {
		self::$instance = $this;
	}

	/**
	 * @return mixed
	 */
	public static function getInstance() : One {
		return self::$instance;
	}
}