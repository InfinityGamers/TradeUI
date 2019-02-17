<?php

namespace InfinityGamers\TradeUI\Commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use InfinityGamers\TradeUI\TradeUI;
class Ah extends Command implements PluginIdentifiableCommand{

        /** @var TradeUI */
        protected $loader;

        /**
         * EventListener constructor.
         *
         * @param TradeUI $loader
         */
        public function __construct(TradeUI $loader){
                parent::__construct("ah", "access the public market ui", "Usage: /shop", []);
                $this->loader = $loader;
        }

        /**
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed
         */
        public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                if($sender instanceof Player){
                        if(count($args) > 0){
                                $this->loader->formManager->sendForm($this->loader->PLAYER_SHOP_UI_ID, $sender, strtolower($args[0]));
                                return true;
                        }else{
                                $this->loader->formManager->sendForm($this->loader->MAIN_UI_ID, $sender);
                                return true;
                        }
                }else{
                        $sender->sendMessage("Can't execute that command here, please go in-game.");
                        return false;
                }
        }

        /**
         * @return Plugin
         */
        public function getPlugin(): Plugin{
                return $this->loader;
        }
}