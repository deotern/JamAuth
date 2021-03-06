<?php
namespace JamAuth\Utils;

use pocketmine\utils\Config;

use JamAuth\Utils\Recipe\JamAuthRecipe;
use JamAuth\Utils\Recipe\SimpleAuthRecipe;
use JamAuth\Utils\Recipe\ServerAuthRecipe;

class Kitchen{
    
    private $foods = [];
    private $recipe;
    public static $TIME_FORMAT = "Y-M-d H:i:s";
    
    public function __construct($plugin){
        $name = strtolower($plugin->getDatabase()->getRule("recipe.name"));
        if($name == null){
            $name = "jamauth";
            $plugin->getDatabase()->setRule("recipe.name", "jamauth");
        }
        $d = $plugin->getDatabase()->getRule("recipe.data");
        if($d == null){
            $d = "";
            $plugin->getDatabase()->setRule("recipe.data");
        }
        $data = json_decode($d, true);
        switch($name){
            case "jamauth":
                $this->recipe = new JamAuthRecipe($data);
                break;
            case "simpleauth":
                $this->recipe = new SimpleAuthRecipe($data);
                break;
            case "serverauth":
                $this->recipe = new ServerAuthRecipe($data);
                break;
            default:
                $this->recipe = new JamAuthRecipe($data);
                break;
        }
        
        $foods = new Config($plugin->getDataFolder()."message.yml", Config::YAML);
        foreach($foods->getAll() as $name => $food){
            foreach($food as $nam => $foo){
                if(is_array($foo)){
                    foreach($foo as $na => $fo){
                        $this->foods[$name.".".$nam.".".$na] = $this->seasoning($fo);
                    }
                }else{
                    $this->foods[$name.".".$nam] = $this->seasoning($foo);
                }
            }
        }
    }
    
    public function getFood($name, $args = []){
        if(empty($msg = $this->foods[$name])){
            return $name;
        }else{
            $i = 0;
            foreach($args as $arg){           
                $msg = str_replace("%$i%", self::getFood($arg), $msg);
                $i++;
            }
            return $msg;
        }
    }
    
    public function getRecipe(){
        return $this->recipe;
    }
    
    public function getSalt($gram){
        $salt = "";
        $cabinet = "abcdefghijklmnopqrstuvwxyz0123456789";
        for($amt = 0; $amt < $gram; $amt++){
            $salt .= $cabinet[rand(0,35)];
        }
        return $salt;
    }
    
    private function seasoning($string){
        return preg_replace_callback(
            "/(\\\&|\&)[0-9a-fk-or]/",
            function($matches){
                return str_replace("§r", "§r§f", str_replace("\\§", "&", str_replace("&", "§", $matches[0])));
            },
            $string
        );
    }
    
    /*public static function constant($string, $name){
        return str_replace(
            array(
                "{PLAYER}",
                "{TIME}",
                "{TOTALPLAYERS}",
                "{MAXPLAYERS}"
            ),
            array(
                $name,
                date(self::$TIME_FORMAT),
                count(self::$plugin->getServer()->getOnlinePlayers()),
                self::$maxP
            ),
            $string
        );
    }*/
}