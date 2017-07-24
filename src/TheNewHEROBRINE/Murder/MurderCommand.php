<?php

namespace TheNewHEROBRINE\Murder;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class MurderCommand extends Command implements PluginIdentifiableCommand {

    /** @var MurderMain $plugin */
    private $plugin;

    /**
     * @param MurderMain $plugin
     */
    public function __construct(MurderMain $plugin) {
        parent::__construct("murder", "Murder minigame main command", "/murder join {arena}|quit|setarena {players} {emeralds}", ["mdr"]);
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if ($sender instanceof Player){
            if (isset($args[0])){
                switch (array_shift($args)) {
                    case "join":
                        if ($arena = $this->getPlugin()->getArenaByName($args[0])){
                            if (!$this->getPlugin()->getServer()->isLevelLoaded($arena)){
                                ;
                            }
                            $this->getPlugin()->getServer()->loadLevel($arena);
                            $arena->join($sender);
                        }
                        else{
                            $this->getPlugin()->sendMessage(TextFormat::RED . "Arena $args[0] doesn't exists!", $sender);
                        }
                        break;
                    case "quit":
                        if ($arena = $this->getPlugin()->getArenaByPlayer($sender)){
                            $arena->quit($sender);
                        }
                        else{
                            $this->getPlugin()->sendMessage(TextFormat::RED . "You aren't in any Murder game!", $sender);
                        }
                        break;
                    case "setarena":
                        $world = $sender->getLevel()->getName();
                        $name = $sender->getName();
                        if ($sender->hasPermission("murder.command.setarena")){
                            if (count($args) == 2 and ctype_digit(implode("", $args))){
                                $this->getPlugin()->getListener()->setspawns[$name][$world] = (int)$args[0];
                                $this->getPlugin()->getListener()->setespawns[$name][$world] = (int)$args[1];
                                $this->getPlugin()->getArenasCfg()->setNested("$world.spawns", []);
                                $this->getPlugin()->getArenasCfg()->setNested("$world.espawns", []);
                                $this->getPlugin()->sendMessage("§eSettaggio di§f $args[0] §espawn per il mondo§f {$sender->getLevel()->getName()} §einiziato", $sender);
                            }
                        }
                        break;
                    //debug
                    case "killall":
                        if ($sender->isOp()){ //too lazy to add a perm
                            foreach ($this->getPlugin()->getArenas() as $arena)
                                foreach ($arena->getWorld()->getEntities() as $entity)
                                    $entity->setHealth(0);
                        }
                        break;
                }
            }
        }
        return true; //TODO
    }

    /** @noinspection PhpDocSignatureInspection */
    /**
     * @return MurderMain
     */
    public function getPlugin(): Plugin{
        return $this->plugin;
    }
}
