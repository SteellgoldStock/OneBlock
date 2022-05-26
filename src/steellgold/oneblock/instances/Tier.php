<?php

namespace steellgold\oneblock\instances;

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

	public static function fromStdClass(mixed $json_decode) : Tier {
		return new Tier(
			$json_decode->id,
			$json_decode->name,
			$json_decode->breakToUp,
			$json_decode->blocks
		);
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

	public function getBlocks(): array {
		return $this->blocks;
	}
}