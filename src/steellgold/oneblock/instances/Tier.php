<?php

namespace steellgold\oneblock\instances;

class Tier {

	/**
	 * @param string $name
	 * @param int $breakToUp
	 * @param array $blocks
	 */
	public function __construct(
		public string $name,
		public int $breakToUp,
		public array $blocks
	) {

	}
}