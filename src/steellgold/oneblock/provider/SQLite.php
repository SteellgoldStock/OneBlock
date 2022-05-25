<?php

use steellgold\oneblock\One;

class SQLite {

	public \SQLite3 $db;

	public function __construct() {
		$this->db = new \SQLite3(One::getInstance()->getDataFolder() . "oneblock.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS islands (
			owner TEXT,
			members TEXT,
			spawn TEXT,
			tier TEXT,
			PRIMARY KEY (owner)
		)");
		$this->db->exec("CREATE TABLE IF NOT EXISTS tiers (breakToUp INT, blocks TEXT,
			PRIMARY KEY (breakToUp)
		)");
	}
}