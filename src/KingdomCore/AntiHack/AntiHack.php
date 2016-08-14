<?php

namespace AntiHack;

use pocketmine\plugin\Plugin;
use pocketmine\Server;

use AntiHack\AntiHackEventListener;
use AntiHack\AntiHackTick;

class AntiHack {

	public $hackScore = array();	

	static private $instance;

	private function __construct() {}	
	private function __clone() {}	
	public function __destruct() {}
	

	static public function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	

	static public function enable(Plugin $plugin) {
		self::getInstance();
		Server::getInstance()->getPluginManager()->registerEvents(
			new AntiHackEventListener(), $plugin
		);
		
		Server::getInstance()->getScheduler()->scheduleRepeatingTask(
			new AntiHackTick(), 40
		);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask(
			new AntiHackSuspicionTick(), 20 * 60
		);
	}		
}
