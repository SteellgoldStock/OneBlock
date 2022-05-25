<?php

namespace steellgold\oneblock\instances;

class Tier {

	/**
	 * @param int $breakToUp
	 * @param array $blocks
	 */
	public function __construct(
		public int $breakToUp,
		public array $blocks,
	) {

	}
}