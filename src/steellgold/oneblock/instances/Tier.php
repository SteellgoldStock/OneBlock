<?php

namespace steellgold\oneblock\instances;

use steellgold\oneblock\utils\Utils;

class Tier {

	/**
	 * @param int $id
	 * @param string $name
	 * @param int $breakToUp
	 * @param array $blocks
	 */
	public function __construct(
		public int    $id,
		public string $name,
		public int    $breakToUp,
		public array  $blocks
	) {

	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getBreakToUp(): int {
		return $this->breakToUp;
	}

	public function getChanceBlock() {
		var_dump($this->blocks);
		return Utils::chancePercentage($this->getBlocks());
	}

	public function getBlocks(): array {
		return $this->blocks;
	}
}