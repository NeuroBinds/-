<?php

namespace Portal;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerMoveEvent;
use KingdomCore\Main;

class PortalListener extends PluginBase implements Listener {

   protected $plugin;

   public function __construct(Main $plugin){
       $this->plugin = $plugin;
   }

   public function onPortal(PlayerMoveEvent $event){
       $player = $event->getPlayer();
   if($player->getLevel()->getName() == "hub"){
   if($event->getFrom()->getLevel()->getBlockIdAt($event->getTo()->x, $event->getTo()->y, $event->getTo()->z) === Block::PORTAL){
      $this->plugin->gamesLobby($player);
    }     
   }
  }
}
