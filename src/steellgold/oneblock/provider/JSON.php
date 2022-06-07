<?php

namespace steellgold\oneblock\provider;

use steellgold\oneblock\One;

class JSON {

	public function __construct() {
		if(!is_dir(One::getInstance()->getDataFolder() . "islands")){
			mkdir(One::getInstance()->getDataFolder() . "islands");
		}
	}

}