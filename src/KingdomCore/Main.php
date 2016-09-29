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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\utils\Color;
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
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\entity\Entity;
use pocketmine\utils\Random;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\block\Block;
use Alert\AlertTask;
use AntiHack\AntiHackEventListener;
use ChatFilter\ChatFilterTask;
use ChatFilter\ChatFilter;

class Main extends PluginBase implements Listener {
 
   public $users = [];

   public function onEnable(){
       $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
       $this->yml = $yml->getAll();
       $this->filter = new ChatFilter();
       $this->getLogger()->info(C::GREEN ."Starting KingdomCraft Core ". C::WHITE . $this->getConfig()->get("Version"));
       $this->getServer()->getPluginManager()->registerEvents($this ,$this);      
       $this->getServer()->loadLevel("PVP"); 
       $this->saveResource("config.yml");
       $this->saveDefaultConfig();
   if($this->getConfig()->get("Dev_Mode") == "true"){
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Dev-Server-Name"));       
   }
       $this->getServer()->getScheduler()->scheduleRepeatingTask(new AlertTask($this), 2000);
       $this->getLogger()->info(C::GOLD ."Alerts Loaded");
       $this->getServer()->getPluginManager()->registerEvents(new AntiHackEventListener(), $this);
       $this->getLogger()->info(C::GOLD ."AntiHacks Loaded");
       $this->getServer()->getScheduler()->scheduleRepeatingTask(new ChatFilterTask($this), 30);
       $this->getLogger()->info(C::GOLD ."ChatFilter Loaded");
       $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name")); 
       $this->getLogger()->info(C::GRAY ."Everything Loaded!");
   }

   public function onRespawn(PlayerRespawnEvent $event){
       $player = $event->getPlayer();
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $this->Items($player);
       $this->setRank($player); 
   }

   public function onJoin(PlayerJoinEvent $event){ 
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn()); 
       $player = $event->getPlayer();
       $level = $this->getServer()->getLevelByName("hub");
       $br = C::RESET . C::WHITE . "\n";
       $text[0] = C::DARK_RED ."[". C::DARK_GRAY ."------------------------------------". C::DARK_RED ."]". $br . C::GRAY ."Welcome to ". C::AQUA ."Kingdom". C::BLUE ."Craft". $br . C::GRAY ."You are Playing on - play.kcmpe.net". $br . C::GRAY ."Hope you Enjoy you Stay!". $br .C::DARK_RED ."[". C::DARK_GRAY ."------------------------------------". C::DARK_RED ."]";
       $text[1] = C::BOLD . C::AQUA ."Kingdom". C::BLUE ."Craft";
       $text[2] = C::AQUA . "Welcome, ". C::WHITE . $player->getName();
       $text[3] = C::AQUA . "There is ". C::WHITE . count($this->getServer()->getOnlinePlayers()) . C::AQUA ." players online";
       $this->Items($player);
       $this->setRank($player); 
       $player->sendMessage($text[0]);
       $level->addParticle(new FloatingTextParticle(new Vector3(170.5505, 67.8, 41.4863), $text[1]. $br . $br .$text[2]. $br . $br .$text[3]), [$event->getPlayer()]);
   }   

   
  public function onItemUse(DataPacketReceiveEvent $event){
       $br = C::RESET . C::WHITE . "\n";
       $pk = $event->getPacket();
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
   if($pk instanceof UseItemPacket and $pk->face === 0xff) {
       $item = $player->getInventory()->getItemInHand();
   if($item->getId() == $this->yml["Games-Item"] and $player->getLevel()->getName() == "hub"){
       $this->gamesLobby($player);
   }
   elseif($item->getId() == $this->yml["Help-Item"] and $player->getLevel()->getName() == "hub"){
       $this->Help($player);
   }
   elseif($item->getId() == $this->yml["Info-Item"] and $player->getLevel()->getName() == "hub"){ 
       $text[1] = C::DARK_PURPLE ."Staff". $br . C::AQUA ."Owners: ". C::WHITE." EnderPE, EpicSteve33". $br . C::AQUA ."Co-Owners: ". C::WHITE ." andrep0617, Realnanners". $br . C::AQUA ."Admins: ". C::WHITE ." caca559, LilBiggs11, EllieDoesGames". $br . C::AQUA ."Others: ". C::WHITE ." SmexiMexiG, PattyWak, GigsfanMC"; 
       $text[2] = C::RED ."§cRules". $br . C::AQUA ."#1: ". C::WHITE ." No Hacking". $br . C::AQUA ."#2: ". C::WHITE ." Do not Harass Other Players or Staff". $br . C::AQUA ."#3: ". C::WHITE ." Do not Ask to be Admin". $br . C::AQUA ."#4: ". C::WHITE ." Do not Share your account info";
       $player->sendMessage($text[1] . $br . $br . $text[2]);
   }
   elseif($item->getId() == $this->yml["Hub-Item"] and $player->getLevel()->getName() == "hub"){
       $player->getLevel()->addSound(new EndermanTeleportSound($player));
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $this->Items($player);
       $this->setRank($player); 
    }
   }
  }

  public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $level = $event->getPlayer()->getLevel();
        $event->setRecipients($player->getLevel()->getPlayers());
  if(!in_array($event->getPlayer()->getDisplayName(), $this->users) && !$this->filter->check($event->getPlayer(), $event->getMessage())) { 
       $event->setCancelled(true);
   }
  }

   public function Commands(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
       $version = $this->getConfig()->get("Version");
       $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
   if($cmd[0] === "/plugins"){
       $player->sendMessage(C::GRAY ."Plugins (3): ". C::GOLD ." KingdomAuth v1.0, KingdomCore ". $version .", SkyWarsCore v1.0");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/?" or $cmd[0] === "/version" or$cmd[0] === "/op" or $cmd[0] === "/deop" or $cmd[0] === "/effect" or $cmd[0] === "/kill" or $cmd[0] === "/enchant" or    $cmd[0] === "/weather" or $cmd[0] === "/summon" or $cmd[0] === "/xp"){
       $player->sendMessage(C::RED ."Unknown command. Try /help for a list of commands");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/help"){ 
       $this->Help($player); 
       $event->setCancelled();
   }  
   elseif($cmd[0] === "/hub" or $cmd[0] === "/lobby" or $cmd[0] === "/spawn"){ 
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $this->Items($player);
       $this->setRank($player); 
       $event->setCancelled();
   }
   elseif($cmd[0] === "/gm"){ 
       $this->HelpGamemode($player); 
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/gms" and $player->isOp()){ 
       $player->setGamemode(0);
       $player->sendMessage(C::GOLD ."Your Gamemode has been updated");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/gmc" and $player->isOp()){ 
       $player->setGamemode(1);
       $player->sendMessage(C::GOLD ."Your Gamemode has been updated");
       $event->setCancelled();
   } 
   elseif($cmd[0] === "/flyon" and $player->isOp() and $player->getLevel()->getName() == "hub" or $cmd[0] === "/fly" and $player->isOp() and $player->getLevel()->getName() == "hub" ){
       $player = $event->getPlayer();
       $player->setAllowFlight(true);
       $player->sendMessage(C::GOLD ."Flight was Turned ". C::GREEN ." On");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/flyon" and !$player->isOp() or $cmd[0] === "/gms" and !$player->isOp() or $cmd[0] === "/gmc" and !$player->isOp()){
       $player = $event->getPlayer();
       $player->sendMessage(C::RED ."Staff Only");
       $event->setCancelled();
   }
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
       $this->setup($player);
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
       $this->setup($player);
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
       $this->setup($player);
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
 
  public function onDeath(PlayerDeathEvent $event)  {
        $event->setDeathMessage("");
        $cause = $event->getEntity()->getLastDamageCause();
  if($cause instanceof EntityDamageByEntityEvent) {
        $player = $event->getEntity();
        $killer = $cause->getDamager();
  if($killer instanceof Player){
  if($player->getLevel()->getName() == "PVP"){
        $killer->sendMessage(C::GOLD ."You Killed ". C::WHITE . $player->getName());
        $player->sendMessage(C::GOLD ."You were Killed by ". C::WHITE . $killer->getName());
        $player->setMaxHealth(20);
        $player->getInventory()->clearAll();
     }
    }
   }
  }

  public function signSetup(SignChangeEvent $event){
      $player = $event->getPlayer();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
      $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign))
  {
  return true;
  }
       $sign = $event->getLines();
  if($sign[0] == "hub" and $player->getLevel()->getName() == "hub"){
       $event->setLine(0, C::DARK_RED ."[". C::GRAY ."-------------". C::DARK_RED ."]");
       $event->setLine(1, C::AQUA ."KingdomCraft");
       $event->setLine(2, C::AQUA ."0.16.0 Alpha");
       $event->setLine(3, C::DARK_RED ."[". C::GRAY ."-------------". C::DARK_RED ."]");
  } 
  elseif($sign[0] == "kit1" and $player->getLevel()->getName() == "hub"){
       $event->setLine(0, C::GRAY ."[" .C::AQUA ."Archer". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit1");
       $event->setLine(3, C::WHITE ."Tap for Kit");
  }
  elseif($sign[0] == "kit2" and $player->getLevel()->getName() == "hub"){
       $event->setLine(0, C::GRAY ."[" .C::RED ."Knight". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit2");
       $event->setLine(3, C::WHITE ."Tap for Kit");
  }
  elseif($sign[0] == "kit3" and $player->getLevel()->getName() == "hub"){
       $event->setLine(0, C::GRAY ."[" .C::GOLD ."Flame". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit3");
       $event->setLine(3, C::WHITE ."Tap for Kit");
  }
  elseif($sign[0] == "coming" and $player->getLevel()->getName() == "hub"){
       $event->setLine(0,"");
       $event->setLine(1, C::WHITE ."More Kits");
       $event->setLine(2, C::WHITE ."Coming Soon");
    }
   }
  }

  public function gamesLobby($player){
       $player->getLevel()->addSound(new EndermanTeleportSound($player));
       $player->sendMessage("-- ". C::AQUA ." Welcome to Games Lobby ". C::WHITE ." --");
       $player->teleport(new Vector3(165, 74, 201));
       $player->setHealth(20);
       $player->setFood(20);
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $player->getInventory()->setItem(8, Item::get(345, 0, 1)->setCustomName(C::GREEN ."Hub"));
  }
 
  public function setup($player){
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->getInventory()->clearAll();
  }
 
  public function setRank($player){
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
       $player->setDisplayName(C::WHITE . $player->getName());
       $player->setNameTag(C::WHITE . $player->getName());
  if($rank == "VIP"){
       $player->setDisplayName(C::GRAY ."[". C::GOLD ."VIP". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE . " ");
       $player->setNameTag(C::GRAY ."[". C::GOLD ."VIP". C::GRAY ."] ". C::AQUA . $player->getName());
  }
  elseif($rank == "Owner"){
       $player->setDisplayName(C::GRAY ."[". C::DARK_PURPLE ."Owner". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE . " ");
       $player->setNameTag(C::GRAY ."[". C::DARK_PURPLE ."Owner". C::GRAY ."] ". C::AQUA . $player->getName());
  }
  elseif($rank == "Co-Owner"){
       $player->setDisplayName(C::GRAY ."[". C::DARK_BLUE ."Co-Owner". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE . " ");
       $player->setNameTag(C::GRAY ."[". C::DARK_BLUE ."Co-Owner". C::GRAY ."] ". C::AQUA . $player->getName());
  }
  elseif($rank == "Admin"){
       $player->setDisplayName(C::GRAY ."[". C::GREEN ."Admin". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE . " "); 
       $player->setNameTag(C::GRAY ."[". C::GREEN ."Admin". C::GRAY ."] ". C::AQUA . $player->getName());
  }
  elseif($rank == "Mobcrush"){
       $player->setDisplayName(C::GRAY ."[". C::YELLOW ."MobCrush". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE . " ");
       $player->setNameTag(C::GRAY ."[". C::YELLOW ."MobCrush". C::GRAY ."] ". C::AQUA . $player->getName());
   }
  }

  public function Help($player){
       $br = C::RESET . C::WHITE . "\n";
       $player->sendMessage(C::AQUA ."== Help Page 1 of 1 == ". $br . C::AQUA ."/hub:". C::WHITE ." Teleport player to hub". $br . C::AQUA ."/help:". C::WHITE ." lists all Commands". $br . C::AQUA ."/msg:". C::WHITE ." {player} Sends a private message to the given player". $br . C::AQUA ."/gm:".C::WHITE ." Allows Staff to Change Gamemode". $br . C::AQUA ."/flyon:". C::WHITE ." Allows Admins to fly");
  }
 
  public function HelpGamemode($player){
       $br = C::RESET . C::WHITE . "\n";
       $player->sendMessage(C::AQUA ."== Gamemode Help Page 1 of 1 ==". $br . C::AQUA ."/gms:". C::WHITE ." Survival Mode". $br . C::AQUA ."/gmc:". C::WHITE ." Creative Mode");
  }


  public function Items($player){
       $player->getInventory()->clearAll();
       $player->getInventory()->setItem(0, Item::get(378, 0, 1)->setCustomName(C::GREEN ."Games"));
       $player->getInventory()->setItem(3, Item::get(369, 0, 1)->setCustomName(C::GREEN ."Parkour"));
       $player->getInventory()->setItem(4, Item::get(340, 0, 1)->setCustomName(C::GREEN ."Info"));
       $player->getInventory()->setItem(5, Item::get(339, 0, 1)->setCustomName(C::GREEN ."Help"));
       $player->getInventory()->setItem(8, Item::get(345, 0, 1)->setCustomName(C::GREEN ."Hub"));
       $player->setGamemode(0);
       $player->setMaxHealth(20);
       $player->setHealth(20);
       $player->setFood(20);
  }

  public function onDisable(){
       $this->getLogger()->info(C::RED ."Shutting down KingdomCraft Core ". C::WHITE . $this->getConfig()->get("Version"));
       $this->getLogger()->info("Done!");
  }
}
