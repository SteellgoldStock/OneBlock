<?php

namespace steellgold\oneblock\commands\subs;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use steellgold\oneblock\One;

class IslandTopPointsCommand extends BaseSubCommand {

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$array = $this->sort_array_of_array(One::getInstance()->getManager()->getIslandsTop());

		$pagedArray = array_chunk($array, 10, true);
		$nthPage = $pagedArray;

		$pageRequested = ($args["page_number"] ?? 0);

		if (!key_exists($pageRequested, $nthPage)) {
			$pageRequested = 0;
		}

		$top = One::getInstance()->getConfig()->get("messages")["points-top-top"];
		if ($pageRequested == 0) {
			$i = 1;
		} else $i = ($pageRequested * 10) + 1;

		foreach ($nthPage[$pageRequested] as $x => $x_value) {
			$island = One::getInstance()->getManager()->getIsland($x);

			$find = [
				"{ID}",
				"{OWNER}",
				"{POINTS}"
			];
			$replace = [
				$i,
				$island->getOwner(),
				$island->getPoints(),
			];

			$top .= "§r\n" . str_replace($find, $replace, One::getInstance()->getConfig()->get("messages")["points-top-line"]);

			$i++;
		}

		$sender->sendMessage($top);
	}

	public function sort_array_of_array(array $array): array {
		$sortarray = array();
		foreach ($array as $key => $row) {
			$sortarray[$key] = $row;
		}

		array_multisort($sortarray, SORT_DESC, $array);
		return $array;
	}

	protected function prepare(): void {
		$this->registerArgument(0, new IntegerArgument("page_number", true));
	}
}