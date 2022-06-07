<?php

namespace steellgold\oneblock\provider;

use steellgold\oneblock\One;

class Text {
	public static function getCommandDescription(string $command){
		return One::getInstance()->getConfig()->get("commands")[$command] ?? "Description not found";
	}

	public static function getMessage(string $identifier, bool $error = false, array $params = [], array $replace = [], string $sub = ""){
		if($sub !== ""){
			return One::getInstance()->getConfig()->get("messages")["prefix"][$error ? "error" : "success"] . str_replace($params,$replace,One::getInstance()->getConfig()->get($identifier)[$sub]) ?? "Message not found";
		}
		return One::getInstance()->getConfig()->get("messages")["prefix"][$error ? "error" : "success"] . str_replace($params,$replace,One::getInstance()->getConfig()->get("messages")[$identifier]) ?? "Message not found";
	}
}