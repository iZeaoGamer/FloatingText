<?php
declare(strict_types=1);

namespace TheNewManu\FloatingText;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\level\particle\FloatingTextParticle;

class Main extends PluginBase {

    /** @var array FloatingTexts[] */
    public $floatingTexts = [];
    public $faction;

    public function onEnable() {
        $this->faction = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
        if($this->faction->isEnabled() && $this->faction instanceof FactionsMain){
        $this->saveDefaultConfig();
        $this->floatingText = new Config($this->getDataFolder() . "floating-text.yml", Config::YAML);
        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new FloatingTextCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new FloatingTextUpdate($this), 20 * $this->getUpdateTime());
        $this->reloadFloatingText();
        }else{
            $this->getLogger()->error("Plugin could not be enabled. Unknown dependency: FactionsPro");
            $this->getServer()->getPluginManager()->disablePlugin($this);      
            
    }
    }
    public function reloadFloatingText() {
        foreach($this->getFloatingTexts()->getAll() as $id => $array) {
            $this->floatingTexts[$id] = new FloatingTextParticle(new Vector3($array["x"], $array["y"], $array["z"]), "");
        }
    }
    
    /**
     * @param Player $player
     * @param string $string
     * @return string
     */
    public function replaceProcess(Player $player, string $string): string {
        $string = str_replace("{line}", TF::EOL, $string);
        $string = str_replace("{player_name}", $player->getName(), $string);
        $string = str_replace("{player_health}", round($player->getHealth(), 1), $string);
        $string = str_replace("{player_max_health}", $player->getMaxHealth(), $string);
        $string = str_replace("{online_players}", count($this->getServer()->getOnlinePlayers()), $string);
        $string = str_replace("{online_max_players}", $this->getServer()->getMaxPlayers(), $string);
        $string = str_replace("{topstr}", $this->faction->sendListOfTop10FactionsTo($player), $string);
        $string = str_replace("{topfacmoney}", $this->faction->sendListOfTop10RichestFactionsTo($player), $string);
        $string = str_replace("{topvalue}", $this->faction->sendListOfTop10ValuedFactionsTo($player), $string);                 
        return $string;
    }
    
    /**
     * @return Config
     */
    public function getFloatingTexts(): Config {
        return $this->floatingText;
    }
    
    /**
     * @return int
     */
    public function getUpdateTime(): int {
        return $this->getConfig()->get("update-time");
    }
}
