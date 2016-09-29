<?php

namespace KitPvP;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat as C;
use KingdomCore\Main;

class PvP extends PluginBase implements Listener {

        protected $plugin;

  public function __construct(Main $plugin){
       $this->plugin = $plugin;
  }

  public function GameSigns(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $kitText[1] = "-- ". C::AQUA ."You are playing with the". C::WHITE . " Archer " . C::AQUA ."kit". C::WHITE ." --";
       $kitText[2] = "-- ". C::AQUA ."You are playing with the". C::WHITE . " Knight " . C::AQUA ."kit". C::WHITE ." --";
       $kitText[3] = "-- ". C::AQUA ."You are playing with the". C::WHITE . " Flame " . C::AQUA ."kit". C::WHITE ." --";
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
       $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign))
  {
  return true;
  }
       $sign = $sign->getText();
  if($sign[1]== C::WHITE ."kit1"){
       $player->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $ItemBow = Item::get(261, 0, 1);
       $ItemBow->setCustomName(C::RED ."Archer Bow");
       $ItemBow->addEnchantment(Enchantment::getEnchantment(19)->setLevel(1));
       $tempTagRed = new CompoundTag("", []);
       $tempTagRed->customColor = new IntTag("customColor", 0xDA2623); 
       $player->sendMessage($kitText[1]);
       $player->sendTip($kitText[1]);
       $this->plugin->setup($player);
       $event->getPlayer()->getInventory()->setHelmet(Item::get(Item::LEATHER_CAP)->setCompoundTag($tempTagRed));
       $event->getPlayer()->getInventory()->setChestplate(Item::get(Item::LEATHER_TUNIC)->setCompoundTag($tempTagRed));
       $event->getPlayer()->getInventory()->setLeggings(Item::get(Item::LEATHER_PANTS)->setCompoundTag($tempTagRed));
       $event->getPlayer()->getInventory()->setBoots(Item::get(Item::LEATHER_BOOTS)->setCompoundTag($tempTagRed));
       $player->setNameTag(C::GRAY ."[" .C::RED ."Archer". C::GRAY ."] ". C::WHITE . $player->getName());
       $player->getInventory()->setItem(0, Item::get(279, 0, 1));
       $player->getInventory()->setItem(1, $ItemBow);
       $player->getInventory()->setItem(2, Item::get(364, 0, 255));
       $player->getInventory()->setItem(10, Item::get(262, 0, 255));
       $player->getInventory()->sendContents($player);
       $player->getInventory()->sendArmorContents($player);
  }
  elseif($sign[1]== C::WHITE ."kit2"){
       $player->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $ItemSword = Item::get(276, 0, 1);
       $ItemSword->setCustomName(C::AQUA ."Knight Sword");
       $ItemSword->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
       $ItemSword->addEnchantment(Enchantment::getEnchantment(12)->setLevel(1));
       $tempTagBlue = new CompoundTag("", []);
       $tempTagBlue->customColor = new IntTag("customColor", 4276384);    
       $player->sendMessage($kitText[1]);
       $player->sendTip($kitText[1]);
       $this->plugin->setup($player);
       $event->getPlayer()->getInventory()->setHelmet(Item::get(Item::LEATHER_CAP)->setCompoundTag($tempTagBlue));
       $event->getPlayer()->getInventory()->setChestplate(Item::get(Item::LEATHER_TUNIC)->setCompoundTag($tempTagBlue));
       $event->getPlayer()->getInventory()->setLeggings(Item::get(Item::LEATHER_PANTS)->setCompoundTag($tempTagBlue));
       $event->getPlayer()->getInventory()->setBoots(Item::get(Item::LEATHER_BOOTS)->setCompoundTag($tempTagBlue));
       $player->setNameTag(C::GRAY ."[" .C::AQUA ."Knight". C::GRAY ."] ". C::WHITE . $player->getName());
       $player->getInventory()->setItem(0, $ItemSword);
       $player->getInventory()->setItem(1, Item::get(364, 0, 255));
       $player->getInventory()->sendContents($player);
       $player->getInventory()->sendArmorContents($player);
  }
  elseif($sign[1]== C::WHITE ."kit3"){
       $player->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $ItemFlame = Item::get(280, 0, 1);
       $ItemFlame->setCustomName(C::GOLD ."Flame Stick");
       $ItemFlame->addEnchantment(Enchantment::getEnchantment(13)->setLevel(2)); 
       $ItemFlame->addEnchantment(Enchantment::getEnchantment(9)->setLevel(3)); 
       $tempTagYellow = new CompoundTag("", []);
       $tempTagYellow->customColor = new IntTag("customColor", 15724314);
       $player->sendMessage($kitText[3]);
       $player->sendTip($kitText[3]);
       $this->plugin->setup($player);
       $event->getPlayer()->getInventory()->setHelmet(Item::get(Item::LEATHER_CAP)->setCompoundTag($tempTagYellow));
       $event->getPlayer()->getInventory()->setChestplate(Item::get(Item::LEATHER_TUNIC)->setCompoundTag($tempTagYellow));
       $event->getPlayer()->getInventory()->setLeggings(Item::get(Item::LEATHER_PANTS)->setCompoundTag($tempTagYellow));
       $event->getPlayer()->getInventory()->setBoots(Item::get(Item::LEATHER_BOOTS)->setCompoundTag($tempTagYellow));
       $player->setNameTag(C::GRAY ."[" .C::GOLD ."Flame". C::GRAY ."] ". C::WHITE . $player->getName());
       $player->getInventory()->setItem(0, $ItemFlame);
       $player->getInventory()->setItem(1, Item::get(364, 0, 255));
       $player->getInventory()->sendContents($player);
       $player->getInventory()->sendArmorContents($player);
    }
   } 
  }
}
