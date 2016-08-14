<?php

namespace AntiHack;

use AntiHack\AntiHack;
use pocketmine\scheduler\Task;


class AntiHackSuspicionTick extends Task {

	private $plugin;
	
	public function __construct() {
		$this->plugin = AntiHack::getInstance();
	}

	public function onRun($currentTick) {
		foreach ($this->plugin->hackScore as $playerId => $data){			
			if($data["suspicion"] > 0){
				$this->plugin->hackScore[$playerId]["suspicion"] --;
			}
		}
	}
	
}