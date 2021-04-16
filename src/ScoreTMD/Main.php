<?php 

namespace ScoreTMD;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\{RemoveObjectivePacket,SetDisplayObjectivePacket,SetScorePacket,types\ScorePacketEntry};
use pocketmine\utils\{Config, TextFormat as TE};

class Main extends PluginBase {
  
public static $instance;
  private $scoreboards = [];
	private $plugin;
  
  public function onEnable() : void {
    self::$instance = $this;
    
    $this->getLogger()->info("ScoreBoard-4.0.0 Enable");
    
  }
  
  public static function getInstance() : self {
    return self::$instace;
  }
  
  public function new(Player $pl, string $objectiveName, string $displayName) : void { 
		if(isset($this->scoreboards[$pl->getName()])){
			$this->remove($pl);
		}
		/* get to packet scoreboard */
		/* and players objetiveName to scoreboard */
		$pk = new SetDisplayObjectivePacket();

		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$pl->getNetworkSession()->sendDataPacket($pk);
		$this->scoreboards[$pl->getName()] = $objectiveName;
	}
  public function remove(Player $pl) : void {
		if(isset($this->scoreboards[$pl->getName()])){
			$objectiveName = $this->getObjectiveName($pl);
			/* remove packet, scoreboard */
			$pk = new RemoveObjectivePacket();
			
			$pk->objectiveName = $objectiveName;
			$pl->getNetworkSession()->sendDataPacket($pk);
			unset($this->scoreboards[$pl->getName()]);
		}
	}
  public function setLine(Player $pl, int $score, string $message) : void {
		if(!isset($this->scoreboards[$pl->getName()])){
			$this->getLogger()->info("You not have set to scoreboards");
			return;
		}
		if($score > 15 || $score < 1){
			$this->getLogger()->info("Error, you exceeded the limit of parameters 1-15");
			return;
		}
		$objectiveName = $this->getObjectiveName($pl);
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $objectiveName;
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$pk = new SetScorePacket();
		
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$pl->getNetworkSession()->sendDataPacket($pk);
	}
  public function getObjectiveName(Player $pl) : ?string {
		return isset($this->scoreboards[$pl->getName()]) ? $this->scoreboards[$pl->getName()] : null;
	}
}
