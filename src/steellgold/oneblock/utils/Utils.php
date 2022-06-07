<?php

namespace steellgold\oneblock\utils;

# https://gist.github.com/Steellgold/ca717ffb1947e316862f000dcc24d9f9
class Utils {
	public static function chancePercentage(array $array = []): mixed {
		$random = mt_rand(1, 100);

		$start = 0;
		$before = 100;
		foreach ($array as $data) {
			$start += $data["chance"];
			$after = $start - $random;

			if (abs($after) < $before) {
				$before = abs($after);
				$dropData = $data;
			}
		}

		return $dropData;
	}
}