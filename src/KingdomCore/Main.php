<?php

namespace KingdomCore;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\TranslationContainer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
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
use pocketmine\entity\Effect;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\network\protocol\AddEntityPacket;
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
use onebone\economyapi\EconomyAPI;
use AntiHack\AntiHack;

class Main extends PluginBase implements Listener{

  //  This Plugin was Created  by EpicSteve33                       \\
 //   This Plugin was Created to Help add more to KingdomCraft       \\
//    This Plugin was not made for any other server then KingdomCraft \\
 
   private $maxcaps;
   public $interval = 10;

   public function onEnable(){
       $this->ip = $this->getServer()->getIp();
       $this->interval = $this->getConfig()->get("interval");
       $this->getServer()->getPluginManager()->registerEvents($this ,$this); 
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name"));       
       $this->getServer()->loadLevel("PVP"); 
       $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
       $this->yml = $yml->getAll();
       $this->getLogger()->info("Starting KingdomCraft Core");
       $this->getLogger()->info("Server is running on IP: " . $this->ip);
       AntiHack::enable($this);
       $this->getLogger()->info("§aAntiHack Loaded!");
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
   public function onDisable(){
       $this->getLogger()->info("Shutting down KingdomCraft Core");
       $this->saveConfig();
       $this->getLogger()->info("Done!");
   if($this->getConfig()->get("Dev_Mode") == "true"){
       $this->getLogger()->info("§cCore is Shutting down...");
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name-Dev"));
       $this->getLogger()->info("§cCore Shut Down!");
    }
   }

   public function Explosion(ExplosionPrimeEvent $event){
       $event->setCancelled(true);
   }

   public function onRespawn(PlayerRespawnEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $event->getPlayer()->getInventory()->clearAll();
       $event->getPlayer()->getInventory()->setItem(1, Item::get(388, 0, 1));
       $event->getPlayer()->getInventory()->setItem(2, Item::get(264, 0, 1));
       $event->getPlayer()->getInventory()->setItem(3, Item::get(265, 0, 1));
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(0, 0);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(1, 1);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(2, 2);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(3, 3);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4); 
       $event->getPlayer()->setMaxHealth(20);
       $event->getPlayer()->setHealth(20);
       $event->getPlayer()->setFood(20); 
   }

   public function onJoin(PlayerJoinEvent $event){ 
       $level = $this->getServer()->getLevelByName("hub");
       $player = $event->getPlayer();
       //$player->sendMessage("§7------------------------------------\n§7[§bKingdom§9News§7] §fWelcome,§b " .$player->getName(). "\n§fBeta §bv1§7.§b3§f is around the corner for §bKingdom§9Craft§f, \nyou can expect some nice new features to come,\n§fLike a new map for §bKitPvP§f and much more\n§7------------------------------------");
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $level = $this->getServer()->getDefaultLevel();
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 69.8, 124),"", "§7------------------------------------"));
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 69.4, 124),"", "§fWelcome, §b{$event->getPlayer()->getName()} §fto §bKingdom§9Craft"), [$event->getPlayer()]);
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 69.1, 124),"", "§fYou are Playing on: §bplay§7.§bkcmcpe§b.§bnet"), [$event->getPlayer()]);
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 68.7, 124),"", "§fWe are in beta §7- §bv1§7.§b3"),[$event->getPlayer()]);
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 68.1, 124),"", "§bHope you Enjoy you Stay!"));
       $level->addParticle(new FloatingTextParticle(new Vector3(129, 67.5, 124),"", "§7------------------------------------"));
       $event->getPlayer()->getInventory()->clearAll();
       $event->getPlayer()->getInventory()->setItem(1, Item::get(388, 0, 1));
       $event->getPlayer()->getInventory()->setItem(2, Item::get(264, 0, 1));
       $event->getPlayer()->getInventory()->setItem(3, Item::get(265, 0, 1));
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(0, 0);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(1, 1);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(2, 2);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(3, 3);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4); 
       $event->getPlayer()->setMaxHealth(20);
       $event->getPlayer()->setHealth(20);
       $event->getPlayer()->setFood(20); 
       $player->sendMessage("§fWelcome,§b " .$player->getName(). " §fto §bKingdom§9Craft §fBeta §bv1§7.§b3"); 
   }

   //Removes need for iProtector
   public function onBlockBreakHub(BlockBreakEvent $event){
         $player = $event->getPlayer();
   if($player->getLevel()->getName() == "hub" and !$player->isOp()) {
          $event->setCancelled(true);
   }
   elseif($player->getLevel()->getName() == "PVP") {
          $player = $event->getPlayer();
          $event->setCancelled(true);
    } 
   }
   public function onBlockPlaceHub(BlockPlaceEvent $event){
       $player = $event->getPlayer();
   if($player->getLevel()->getName() == "hub" and !$player->isOp()) {
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
   public function onHunger(PlayerHungerChangeEvent $event){
          $player = $event->getPlayer();
   if($player->getLevel()->getName() == "hub") {
          $event->setCancelled(true);
    }
   }
   public function onDrop(PlayerDropItemEvent $event){
       $player = $event->getPlayer();
       $player->sendTip("§cYou Cannot Drop Items");
       $event->getPlayer()->getLevel()->addSound(new AnvilFallSound($player));
       $event->setCancelled(true);
   }

   public function onHeld(PlayerItemHeldEvent $event){
       $cfg = $this->getConfig();
       $player = $event->getPlayer();
       $item = $event->getItem()->getId();     
   if($item === $cfg->get("item1") and $player->getLevel()->getName() == "hub"){
       $player->sendPopup("KitPvP");
       $level->addSound(new AnvilFallSound($player));
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

   public function onPacketReceived(DataPacketReceiveEvent $event){
       $pk = $event->getPacket();
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
   if($pk instanceof UseItemPacket and $pk->face === 0xff) {
       $item = $player->getInventory()->getItemInHand();
   if($item->getId() == $this->yml["item1"] and $player->getLevel()->getName() == "hub"){
       $player->teleport(new Vector3(119, 77, 81));
       $player->getInventory()->clearAll();
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
   }
   elseif($item->getId() == $this->yml["item4"] and $player->getLevel()->getName() == "hub"){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->sendMessage($this->getConfig()->get("Hub-Command")); 
       $event->getPlayer()->getInventory()->clearAll();
       $event->getPlayer()->getInventory()->setItem(1, Item::get(388, 0, 1));
       $event->getPlayer()->getInventory()->setItem(2, Item::get(264, 0, 1));
       $event->getPlayer()->getInventory()->setItem(3, Item::get(265, 0, 1));
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(0, 0);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(1, 1);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(2, 2);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(3, 3);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4); 
       $event->getPlayer()->setMaxHealth(20);
       $event->getPlayer()->setHealth(20);
       $event->getPlayer()->setFood(20); 
       $level->addSound(new EndermanTeleportSound($player));
       }
     }
   }

   public function onChat(PlayerChatEvent $event){
        $this->maxcaps = intval($this->getConfig()->get("max-caps"));
        $player = $event->getPlayer();
        $event->setRecipients($player->getLevel()->getPlayers());
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

  public function onDeath(PlayerDeathEvent $event)  {
        $cause = $event->getEntity()->getLastDamageCause();
  if($cause instanceof EntityDamageByEntityEvent) {
        $player = $event->getEntity();
        $p = $event->getEntity();
        $killer = $cause->getDamager();
  if ($killer instanceof Player){
        $event->setDeathMessage("");
        $killer->sendMessage("§bYou Killed§f ". $player->getName());
        $player->sendMessage("§bYou were Killed by§f ". $killer->getName() .", §bThey had§f ". $killer->getHealth() ."§7/§f". $killer->getMaxHealth());
        $player->setMaxHealth(20);
        $player->getInventory()->clearAll();
	}
     }
  }

   public function FlyonCommand(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
   if($cmd[0] === "/flyon"){
       $player = $event->getPlayer();
   if($player->isOp()){
       $player->setAllowFlight(true);
      $player->sendMessage("§7[§l§o§bKingdom§9Craft§r§7] §3Flight on");
       $event->setCancelled();
       }
      }
     }

   public function FlyoffCommand(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
   if($cmd[0] === "/flyoff"){
       $player = $event->getPlayer();
   if($player->isOp()){
       $player->setAllowFlight(false);
      $player->sendMessage("§7[§l§o§bKingdom§9Craft§r§7] §cFlight off");
       $event->setCancelled();
       }
      }
     }

   public function Hub(PlayerCommandPreprocessEvent $event) {
       $cmd3 = explode(" ", strtolower($event->getMessage()));
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
   if($cmd3[0] === "/hub" or $cmd3[0] === "/lobby" or $cmd3[0] === "/spawn"){ 
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->sendMessage($this->getConfig()->get("Hub-Command")); 
       $event->getPlayer()->getInventory()->clearAll();
       $event->getPlayer()->getInventory()->setItem(1, Item::get(388, 0, 1));
       $event->getPlayer()->getInventory()->setItem(2, Item::get(264, 0, 1));
       $event->getPlayer()->getInventory()->setItem(3, Item::get(265, 0, 1));
       $event->getPlayer()->getInventory()->setItem(4, Item::get(406, 0, 1));
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(0, 0);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(1, 1);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(2, 2);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(3, 3);
       $event->getPlayer()->getInventory()->setHotbarSlotIndex(4, 4); 
       $event->getPlayer()->setMaxHealth(20);
       $event->getPlayer()->setHealth(20);
       $event->getPlayer()->setFood(20);
       $event->setCancelled();
        }
      }

   public function disabledCommands(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
       $player = $event->getPlayer();
   if($cmd[0] === "/setgroup"){ 
       $player->sendMessage($this->getConfig()->get("Unknown-Command"));
       $event->setCancelled();
   }
   elseif($cmd[0] === "/plugins"){
       $player->sendMessage("§7Plugins (4):  §3KingdomAuth v0.1, KingdomCore v1.3, SkyWarsCore v0.1, SurvivalGamesCore v0.1");
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
   elseif($cmd[0] === "/setworldspawn"){
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
    }

   public function Help(PlayerCommandPreprocessEvent $event) {
       $cmdHelp = explode(" ", strtolower($event->getMessage()));
       $player = $event->getPlayer();
   if($cmdHelp[0] === "/help"){ 
       $player->sendMessage("§o§l§b-- Help Page 1 of 1 --§r\n§b/hub - §fTeleport player to hub\n§b/help - §f{Page} lists all Commands\n§b/msg - §f{player} Sends a private message to the given player\n§b/mymoney - §fChecks How much money you have\n§b/pay - §f{player} Allows you to pay players money\n§b/flyon - §fAdmins only\n§b/flyoff - §fAdmins only");
       $event->setCancelled();
         }
      }

  public function KitSignSetup(SignChangeEvent $event){
      $player = $event->getPlayer();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
                return true;
  }
            $sign = $event->getLines();
  if($sign[0]=='PvP'){
       $player->sendMessage("§o§l§b-- PvP Setup --");
       $event->setLine(0,"§l§c[§bKitPvP§c]");
       $event->setLine(1,"§l§eBiomePvP");
       $event->setLine(3,"§fTap to Join");
    }
   }
  }

  public function SkyWarsSign(SignChangeEvent $event){
      $player = $event->getPlayer();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return true;
  }
            $sign = $event->getLines();
  if($sign[0]=='Sky'){
       $player->sendMessage("§o§l§b-- Skywars Setup --");
       $event->setLine(0,"§l§c[§bSkywars§c]");
       $event->setLine(1,"§l§eSkywars Lobby");
       $event->setLine(3,"§fTap to Join");
    }
   }
  }

  public function playerPvP(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§l§c[§bKitPvP§c]'){
       $player->sendMessage("§o§l§f-- §cJoining §bPvP§f --§r");
       $player->setHealth(20);
       $player->setFood(20);
       $player->teleport(new Vector3(119, 77, 81));
       $player->getInventory()->clearAll();
    }
   }
  }

  public function playerSkywars(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§l§c[§bSkywars§c]'){
       $player->sendMessage("§o§l§f-- §cJoining §bSkywars§f --§r");
       $player->setHealth(20);
       $player->setFood(20);
       $player->teleport(new Vector3(134, 77, 81));
       $player->getInventory()->clearAll();
    }
   }
  }

  public function playerBasicKit1(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§eKnight'){
  if($player->hasPermission("game.kit")){
       $player->sendTip("§o§l§b-- §cPvP Kit §bKnight§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
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
    }
   } 
  }

  public function playerBasicKit2(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§eArcher'){
  if($player->hasPermission("game.kit")){
       $player->sendMessage("§o§l§b-- §cPvP Kit §bArcher§c Given§b --");
       $player->sendTip("§o§l§b-- §cPvP Kit §bArcher§c Given --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
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
    }
   } 
  }

  public function playerMobcrushKit(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§eSuper'){
  if($player->hasPermission("game.kit.super")){
       $player->sendTip("§o§l§b-- §cPvP Kit §bSuper§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
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
    }
   } 
  }

  public function playerVIPKit(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§eSuper+'){
  if($player->hasPermission("game.kit.super+")){
       $player->sendTip("§o§l§b-- §cPvP Kit §bSuper+§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
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
    }
   } 
  }

  public function playerSecretKit(PlayerInteractEvent $event){
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign)){
  return;
  }
       $sign = $sign->getText();
  if($sign[0]=='§eSecretKit'){
  if($player->hasPermission("game.kit")){
       $player->sendTip("§o§l§b-- §cPvP Kit §bSecret§c Given§b --");
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(279,0,1));
       $player->getInventory()->setItem(1, Item::get(466,0,2));
       $player->getInventory()->setItem(2, Item::get(373,14,1));
       $player->getInventory()->setItem(3, Item::get(373,31,1));
       $player->getInventory()->setHelmet(Item::get(302, 0, 1));
       $player->getInventory()->setChestplate(Item::get(307, 0, 1));
       $player->getInventory()->setLeggings(Item::get(308, 0, 1));
       $player->getInventory()->setBoots(Item::get(305, 0, 1));
       $player->getInventory()->sendArmorContents($player);
       $player->getInventory()->setHotbarSlotIndex(0, 0);
       $player->getInventory()->setHotbarSlotIndex(1, 1);
       $player->getInventory()->setHotbarSlotIndex(2, 2);
       $player->getInventory()->setHotbarSlotIndex(3, 3);
       $player->getInventory()->setHotbarSlotIndex(4, 4);
     }
    }
   } 
  }

  public function getMaxCaps(){
       return $this->maxcaps;
  }
  public function saveConfig(){
       $this->getConfig()->set("max-caps", $this->getMaxCaps());
       $this->getConfig()->save();
   }
 }
