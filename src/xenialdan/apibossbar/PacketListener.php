<?php

namespace xenialdan\apibossbar;

use InvalidArgumentException;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use steellgold\oneblock\One;

class PacketListener implements Listener {

	private static ?Plugin $registrant;

	public static function register() {
		One::getInstance()->getServer()->getPluginManager()->registerEvents(new self, One::getInstance());
	}

	public function onDataPacketReceiveEvent(DataPacketReceiveEvent $e) {
		if ($e->getPacket() instanceof BossEventPacket) $this->onBossEventPacket($e);
	}

	private function onBossEventPacket(DataPacketReceiveEvent $e) {
		if (!($pk = $e->getPacket()) instanceof BossEventPacket) throw new InvalidArgumentException(get_class($e->getPacket()) . " is not a " . BossEventPacket::class);
		/** @var BossEventPacket $pk */
		switch ($pk->eventType) {
			case BossEventPacket::TYPE_REGISTER_PLAYER:
			case BossEventPacket::TYPE_UNREGISTER_PLAYER:
				Server::getInstance()->getLogger()->debug("Got BossEventPacket " . ($pk->eventType === BossEventPacket::TYPE_REGISTER_PLAYER ? "" : "un") . "register by client for player id " . $pk->playerActorUniqueId);
				break;
			default:
				$e->getOrigin()->getPlayer()->kick("Invalid packet received", false);
		}
	}

}