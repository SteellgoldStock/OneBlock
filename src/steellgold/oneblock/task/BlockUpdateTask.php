<?php

namespace steellgold\oneblock\task;

use pocketmine\block\Block;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class BlockUpdateTask extends Task {

	public function __construct(
		public Block    $block,
		public Position $position
	) {

	}

	public function onRun(): void {
		$this->position->getWorld()->setBlock($this->position, $this->block);
	}
}