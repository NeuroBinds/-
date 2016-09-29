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
       $x = round($player->getX());
       $z = round($player->getZ());
   if($player->getLevel()->getName() == "hub"){
   if($event->getFrom()->getLevel()->getBlockIdAt($event->getTo()->x, $event->getTo()->y, $event->getTo()->z) === Block::PORTAL){
   if($z = 97 || $z = 96 || $z = 95 || $x = 172 || $x = 171 || $x = 170 || $x = 169 || $x = 168 || $x = 167 || $x = 166 || $x = 165 || $x = 164 || $x = 163 || $x = 162){
      $this->plugin->gamesLobby($player);
    }     
   }
  }
 }
}
