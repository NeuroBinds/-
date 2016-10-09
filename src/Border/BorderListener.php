<?php

namespace Border;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\TextFormat as C;
use KingdomCore\Main;

class BorderListener extends PluginBase implements Listener {

   protected $plugin;

   public function __construct(Main $plugin){
       $this->plugin = $plugin;
   }

   public function onBorder(PlayerMoveEvent $event){
       $player = $event->getPlayer();
       $y = $event->getTo()->getFloorY();
       $z = $event->getFrom()->getFloorZ();
       $x = $event->getFrom()->getFloorX();
   if($player->getLevel()->getName() == "hub" and $y < 63 || $y > 86){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn()); 
       $player->sendPopup(C::RED ."Sorry but you cannot go here!");
   }
   elseif($player->getLevel()->getName() == "hub" and $z < 33 || $z > 99 || $x > 200 || $x < 115){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn()); 
       $player->sendPopup(C::RED ."Woah You can't leave Spawn!");
     }
    }
}  