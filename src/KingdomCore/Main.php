<?php

namespace KingdomCore;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\event\plugin\PluginDisableEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\TranslationContainer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use AntiCheatPE\tasks\SettingsTask;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\level\Position\getLevel;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\Plugin;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use pocketmine\utils\Random;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\block\Block;
use Particle\ParticleManager;

class Main extends PluginBase implements Listener {
 
   private $maxcaps;
   public $interval = 10;

   public function onEnable(){
       $version = $this->getConfig()->get("Version");
       $this->interval = $this->getConfig()->get("interval");
       $this->getServer()->getPluginManager()->registerEvents($this ,$this);
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name"));       
       $this->getServer()->loadLevel("PVP"); 
       $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
       $this->yml = $yml->getAll();
       $this->getLogger()->info("Starting KingdomCraft Core §b". $version);
       $this->getLogger()->info("Done!");
       $this->saveResource("config.yml");
       $this->saveDefaultConfig();
   if($this->getConfig()->get("Dev_Mode") == "true"){
       $this->getLogger()->info("§cDev Mode is Starting up...");
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name-Dev"));
       $this->getLogger()->info("§cDev Mode Loaded!");
    }
   }
   public function loadConfig(){
       $this->saveDefaultConfig();
       $this->maxcaps = intval($this->getConfig()->get("max-caps"));
   }

   public function onRespawn(PlayerRespawnEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $this->setupInventory($player);
       $this->setRank($player); 
   }

   public function onJoin(PlayerJoinEvent $event){ 
       $level = $this->getServer()->getLevelByName("hub");
       $ip = $this->getConfig()->get("Server-IP");
       $version = $this->getConfig()->get("Version");
       $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $level = $this->getServer()->getDefaultLevel();
       $player->sendMessage("§b------------------------------------"); 
       $player->sendMessage("§7Welcome, §b" . $player->getName() . " §7to §bKingdom§9Craft §b". $version); 
       $player->sendMessage("§7You are Playing on: §b". $ip); 
       $player->sendMessage("§7Hope you Enjoy you Stay!"); 
       $player->sendMessage("§b------------------------------------"); 
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $this->setupInventory($player);
       $this->setRank($player); 
   if($rank == "Admin" or $rank == "Mobcrush" or $rank == "Co-Owner" or $rank == "Owner" or $rank == "VIP"){
       $player->sendMessage("§7Welcome back, Your Rank: §b" . $rank);  
       $player->sendMessage("§b------------------------------------"); 
    }
   }

   public function onBlockBreakHub(BlockBreakEvent $event){
         $player = $event->getPlayer();
         $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
         $rank = $rankyml->get($player->getName());
   if($player->getLevel()->getName() == "hub" and !$rank == "Owner" or $player->getLevel()->getName() == "hub" and !$rank == "Co-Owner") {
          $event->setCancelled(true);
   }
   elseif($player->getLevel()->getName() == "PVP") {
          $player = $event->getPlayer();
          $event->setCancelled(true);
    } 
   }
   public function onBlockPlaceHub(BlockPlaceEvent $event){
         $player = $event->getPlayer();
         $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
         $rank = $rankyml->get($player->getName());
   if($player->getLevel()->getName() == "hub" and !$rank == "Owner" or $player->getLevel()->getName() == "hub" and !$rank == "Co-Owner") {
          $event->setCancelled(true);
   }
   elseif($player->getLevel()->getName() == "PVP") {
          $player = $event->getPlayer();
          $event->setCancelled(true);
    } 
   }
   public function GodMode(EntityDamageEvent $event){
          $player = $event->getEntity();
   if($player->getLevel()->getName() == "hub") {
          $event->setCancelled(true);
    } 
   }
   public function onHungerEvent(PlayerHungerChangeEvent $event){
          $player = $event->getPlayer();
   if($player->getLevel()->getName() == "hub") {
          $event->setCancelled(true);
    }
   }
   public function onDropItemEvent(PlayerDropItemEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
       $player->sendTip("§cYou Cannot Drop Items");
       $event->setCancelled(true);
   }

   public function onItemHotbar(PlayerItemHeldEvent $event){
       $cfg = $this->getConfig();
       $player = $event->getPlayer();
       $item = $event->getItem()->getId();     
   if($item === $cfg->get("item1") and $player->getLevel()->getName() == "hub"){
       $player->sendPopup("KitPvP");
   }
   elseif($item === $cfg->get("item2") and $player->getLevel()->getName() == "hub"){
       $player->sendPopup("Help");
   }
   elseif($item === $cfg->get("item3") and $player->getLevel()->getName() == "hub"){
       $player->sendPopup("SkyWars");
   }
   elseif($item === $cfg->get("item4") and $player->getLevel()->getName() == "hub"){
       $player->sendPopup("Hub");
       }
   }

   public function onItemUse(DataPacketReceiveEvent $event){
       $pk = $event->getPacket();
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
   if($pk instanceof UseItemPacket and $pk->face === 0xff) {
       $item = $player->getInventory()->getItemInHand();
   if($item->getId() == $this->yml["item1"] and $player->getLevel()->getName() == "hub"){
       $player->teleport(new Vector3(119, 77, 81));
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4);
   }
   elseif($item->getId() == $this->yml["item2"] and $player->getLevel()->getName() == "hub"){
       $player->sendMessage("§o§l§b-- Help Page 1 of 1 --§r\n§b/hub - §fTeleport player to hub\n§b/help - §f{Page} lists all Commands\n§b/tell - §f{player} Sends a private message to the given player\n§b/mymoney - §fChecks How much money you have\n§b/pay - §f{player} Allows you to give toher players money\n§b/flyon - §fAdmins only\n§b/flyoff - §fAdmins only");
   }
   elseif($item->getId() == $this->yml["item3"] and $player->getLevel()->getName() == "hub"){
       $player->sendMessage("§o§l§f-- §cJoining §bSkywars§f --§r");
       $player->setHealth(20);
       $player->setFood(20);
       $player->teleport(new Vector3(134, 77, 81));
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4);
   }
   elseif($item->getId() == $this->yml["item4"] and $player->getLevel()->getName() == "hub"){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->sendMessage($this->getConfig()->get("Hub-Command")); 
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $this->setupInventory($player);
       }
     }
   }

   public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $level = $event->getPlayer()->getLevel();
   if($this->getConfig()->get("PerWorldChat") == "true"){
        $event->setRecipients($player->getLevel()->getPlayers());
     }
   }


  public function onDeath(PlayerDeathEvent $event)  {
        $cause = $event->getEntity()->getLastDamageCause();
  if($cause instanceof EntityDamageByEntityEvent) {
        $player = $event->getEntity();
        $p = $event->getEntity();
        $killer = $cause->getDamager();
  if($killer instanceof Player){
        $event->setDeathMessage("");
        $killer->sendMessage("§bYou Killed§f ". $player->getName());
        $player->sendMessage("§bYou were Killed by§f ". $killer->getName());
        $player->setMaxHealth(20);
        $this->setRank($player); 
        $player->getInventory()->clearAll();
	}
      }
   }

   public function Commands(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
       $version = $this->getConfig()->get("Version");
       $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
   if($cmd[0] === "/plugins"){
       $player->sendMessage("§7Plugins (4):  §3KingdomAuth v1.0, KingdomCore ". $version .", SkyWarsCore v1.0, SurvivalGamesCore v1.0");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/?"){
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled();
   }
   elseif($cmd[0] === "/effect"){
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/give"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/kill"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/enchant"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled();
   }
   elseif($cmd[0] === "/weather"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/summon"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled();
   }
   elseif($cmd[0] === "/xp"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled();
   }
   elseif($cmd[0] === "/help"){ 
       $player->sendMessage("§o§l§b-- Help Page 1 of 1 --§r\n§b/hub - §fTeleport player to hub\n§b/help - §f{Page} lists all Commands\n§b/msg - §f{player} Sends a private message to the given player\n§b/mymoney - §fChecks How much money you have\n§b/pay - §f{player} Allows you to pay players money\n§b/flyon - §fAllows Admins to fly");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/hub" or $cmd[0] === "/lobby" or $cmd[0] === "/spawn"){ 
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->sendMessage($this->getConfig()->get("Hub-Command")); 
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $this->setupInventory($player);
       $this->setRank($player); 
       $event->setCancelled();
   }
   elseif($cmd[0] === "/flyon" and $player->isOp() and $player->getLevel()->getName() == "hub"){
       $player = $event->getPlayer();
       $player->setAllowFlight(true);
       $player->sendMessage("§6Flight was Turned §aOn");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/flyon" and !$player->isOp()){
       $player = $event->getPlayer();
       $player->sendMessage("§6Flight is only for §aAdmins");
       $event->setCancelled();
    }
   }

  public function SignSetup(SignChangeEvent $event){
      $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
                return true;
  }
            $sign = $event->getLines();
  if($sign[0] == "PvP" and $rank == "Admin" or $sign[0] == "PvP" and $rank == "Owner"){
       $player->sendMessage("§o§l§b-- PvP Setup --");
       $event->setLine(0,"§l§c[§bKitPvP§c]");
       $event->setLine(1,"§l§eBiomePvP");
       $event->setLine(3,"§fTap to Join");
  }
  elseif($sign[0] == "Sky" and $rank == "Admin" or $sign[0] == "Sky" and $rank == "Owner"){
       $player->sendMessage("§o§l§b-- Skywars Setup --");
       $event->setLine(0,"§l§c[§bSkywars§c]");
       $event->setLine(1,"§l§eSkywars Lobby");
       $event->setLine(3,"§fTap to Join");
    }
   }
  }

  public function GameSigns(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§bSkywars'){
       $player->sendMessage("§o§l§f-- §cJoining §bSkywars§f --§r");
       $player->setHealth(20);
       $player->setFood(20);
       $player->teleport(new Vector3(134, 77, 81));
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4);
  }
  elseif($sign[0]=='§bKitPvP§'){
       $player->sendMessage("§o§l§f-- §cJoining §bPvP§f --§r");
       $player->setHealth(20);
       $player->setFood(20);
       $player->teleport(new Vector3(119, 77, 81));
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4);
  }
  elseif($sign[0] == "§eKnight"){
       $player->sendTip("§o§l§b-- §cPvP Kit §bKnight§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->setGamemode(0);
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(276,0,1));
       $player->getInventory()->setItem(1, Item::get(322,0,64));
       $player->getInventory()->setItem(2, Item::get(373,14,1));
       $player->getInventory()->setItem(3, Item::get(373,28,1));
       $player->getInventory()->setHelmet(Item::get(302, 0, 1));
       $player->getInventory()->setChestplate(Item::get(307, 0, 1));
       $player->getInventory()->setLeggings(Item::get(308, 0, 1));
       $player->getInventory()->setBoots(Item::get(305, 0, 1));
       $player->getInventory()->sendArmorContents($player);
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4);
  }
  elseif($sign[0] == "§eArcher"){
       $player->sendMessage("§o§l§b-- §cPvP Kit §bArcher§c Given§b --");
       $player->sendTip("§o§l§b-- §cPvP Kit §bArcher§c Given --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->setGamemode(0);
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(279,0,1));
       $player->getInventory()->setItem(1, Item::get(261,0,1));
       $player->getInventory()->setItem(2, Item::get(322,0,64));
       $player->getInventory()->setItem(3, Item::get(373,14,1));
       $player->getInventory()->setItem(4, Item::get(373,28,1));
       $player->getInventory()->setItem(14, Item::get(262,27,255));
       $player->getInventory()->setHelmet(Item::get(302, 0, 1));
       $player->getInventory()->setChestplate(Item::get(307, 0, 1));
       $player->getInventory()->setLeggings(Item::get(308, 0, 1));
       $player->getInventory()->setBoots(Item::get(305, 0, 1));
       $player->getInventory()->sendArmorContents($player);
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4);
  }
  elseif($sign[0] == "§eSuper" and $rank == "Mobcrush" or $sign[0] == "§eSuper" and $rank == "Admin" or $sign[0] == "§eSuper" and $rank == "Owner"){
       $player->sendTip("§o§l§b-- §cPvP Kit §bSuper§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->setGamemode(0);
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(279,0,1));
       $player->getInventory()->setItem(1, Item::get(466,0,5));
       $player->getInventory()->setItem(2, Item::get(373,14,1));
       $player->getInventory()->setItem(3, Item::get(373,31,1));
       $player->getInventory()->setHelmet(Item::get(302, 0, 1));
       $player->getInventory()->setChestplate(Item::get(311, 0, 1));
       $player->getInventory()->setLeggings(Item::get(308, 0, 1));
       $player->getInventory()->setBoots(Item::get(313, 0, 1));
       $player->getInventory()->sendArmorContents($player);
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4);
  } 
  elseif($sign[0] == "§eSuper+" and $rank == "VIP" or $sign[0] == "§eSuper+" and $rank == "Admin" or $sign[0] == "§eSuper+" and $rank == "Owner"){
       $player->sendTip("§o§l§b-- §cPvP Kit §bSuper+§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->setGamemode(0);
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(279,0,1));
       $player->getInventory()->setItem(1, Item::get(466,0,5));
       $player->getInventory()->setItem(2, Item::get(373,14,1));
       $player->getInventory()->setItem(3, Item::get(373,31,1));
       $player->getInventory()->setHelmet(Item::get(302, 0, 1));
       $player->getInventory()->setChestplate(Item::get(311, 0, 1));
       $player->getInventory()->setLeggings(Item::get(308, 0, 1));
       $player->getInventory()->setBoots(Item::get(313, 0, 1));
       $player->getInventory()->sendArmorContents($player);
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4);
  }
  elseif($sign[0] == "§eSecretKit"){
       $player->sendMessage("§cSorry but this was Removed");
       $player->sendTip("§cSorry but this was Removed");
     }
    } 
   }

   public function setRank($player){
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
   if($rank == "VIP") {
       $player->setDisplayName("§7[§6VIP§7] §b". $player->getName() ." §f");
       $player->setNameTag("§7[§6VIP§7] §b". $player->getName());
   }
   elseif($rank == "Owner") {
       $player->setDisplayName("§7[§5Owner§7] §b". $player->getName() ." §f");
       $player->setNameTag("§7[§5Owner§7] §b". $player->getName());
   }
   elseif($rank == "Co-Owner") {
       $player->setDisplayName("§7[§1Co-Owner§7] §b". $player->getName() ." §f");
       $player->setNameTag("§7[§5Owner§7] §b". $player->getName());
   }
   elseif($rank == "Admin") {
       $player->setDisplayName("§7[§aAdmin§7] §b". $player->getName() ." §f"); 
       $player->setNameTag("§7[§aAdmin§7] §b". $player->getName());
   }
   elseif($rank == "Mobcrush") {
       $player->setDisplayName("§7[§eMobCrush§7] §b". $player->getName() ." §f");
       $player->setNameTag("§7[§eMobCrush§7] §b". $player->getName());
   }
  }

  public function setupInventory($player){
       $player->getInventory()->setItem(1, Item::get(388, 0, 1));
       $player->getInventory()->setItem(2, Item::get(264, 0, 1));
       $player->getInventory()->setItem(3, Item::get(265, 0, 1));
       $player->getInventory()->setItem(4, Item::get(406, 0, 1));
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4); 
       $player->setMaxHealth(20);
       $player->setHealth(20);
       $player->setFood(20);
  }

  public function setupSGInventory($player){
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
  if($rank == "VIP" or $rank == "Mobcrush" or $rank == "Owner" or $rank == "Co-Owner" or $rank == "Admin") {
       $player->setFood(20);
       $player->setHealth(20);
       $player->getInventory()->setArmorItem(0, Item::get(Item::CHAIN_HELMET));
       $player->getInventory()->setArmorItem(1, Item::get(Item::CHAIN_CHESTPLATE));
       $player->getInventory()->setArmorItem(2, Item::get(Item::CHAIN_LEGGINGS));
       $player->getInventory()->setArmorItem(3, Item::get(Item::CHAIN_BOOTS));
       $player->getInventory()->sendArmorContents($player);
       $player->getInventory()->addItem(Item::get(Item::DIAMOND_AXE, 0, 1));
       $player->getInventory()->addItem(Item::get(322, 0, 8));
       $player->getInventory()->sendContents($player);
   }
  }

   public function spamCheck(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $this->maxcaps = intval($this->getConfig()->get("max-caps"));
        $message = $event->getMessage();
        $strlen = strlen($message);
        $asciiA = ord("A");
        $asciiZ = ord("Z");
        $count = 0;
   for($i = 0; $i < $strlen; $i++){
        $char = $message[$i];
        $ascii = ord($char);
   if($asciiA <= $ascii and $ascii <= $asciiZ){
             $count++;
      }
   }
   if ($count > $this->getMaxCaps()) {
        $event->setCancelled(true);
        $player->sendMessage("§7[§bKingdom§9Chat§7] §cYou used too much caps!");
   }
  }
  public function getMaxCaps(){
       return $this->maxcaps;
  }
  public function saveConfig(){
       $this->getConfig()->set("max-caps", $this->getMaxCaps());
       $this->getConfig()->save();
  }

   public function onDisable(){
       $version = $this->getConfig()->get("Version");
       $this->getLogger()->info("Shutting down KingdomCraft Core §b". $version);
       $this->saveConfig();
       $this->getLogger()->info("Done!");
   if($this->getConfig()->get("Dev_Mode") == "true"){
       $this->getLogger()->info("§cCore is Shutting down...");
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name-Dev"));
       $this->getLogger()->info("§cCore Shut Down!");
     }
   }
 }
