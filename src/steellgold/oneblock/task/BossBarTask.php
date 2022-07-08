<?php

namespace steellgold\oneblock\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use steellgold\oneblock\instances\Session;
use steellgold\oneblock\One;

class BossBarTask extends Task {

	public function onRun(): void {
		foreach (One::getInstance()->getManager()->getIslands() as $islands) {
			$islands->updateBossbar();
		}
	}
}