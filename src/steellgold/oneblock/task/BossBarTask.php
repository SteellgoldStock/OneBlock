<?php

namespace steellgold\oneblock\task;

use JsonException;
use pocketmine\scheduler\Task;
use steellgold\oneblock\One;

class BossBarTask extends Task {

	/**
	 * @throws JsonException
	 */
	public function onRun(): void {
		foreach (One::getInstance()->getManager()->getIslands() as $islands) {
			$islands->updateBossbar();
			$islands->save();
		}
	}
}