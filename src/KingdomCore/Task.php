<?php

namespace KingdomCore;

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class Task extends PluginTask {

    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($currentTick){
    	$this->plugin = $this->getOwner();
          	}
}
?>
