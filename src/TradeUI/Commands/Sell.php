<?php

namespace TradeUI\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

use TradeUI\TradeUI;
use TradeUI\Utils\RandomUtils;

class Sell extends Command implements PluginIdentifiableCommand{

        /** @var TradeUI */
        protected $loader;

        /**
         *
         * EventListener constructor.
         *
         * @param TradeUI $loader
         *
         */
        public function __construct(TradeUI $loader){
                parent::__construct("sell", "access the sell ui", "Usage: /sell", []);
                $this->loader = $loader;
        }

        /**
         *
         * @param CommandSender $sender
         * @param string        $commandLabel
         * @param string[]      $args
         *
         * @return mixed
         *
         */
        public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
                if($sender instanceof Player){
                        if(count($sender->getInventory()->getContents()) === 0){
                                $sender->sendMessage(RandomUtils::colorMessage($this->loader->getMessage('no_empty')));
                                return false;
                        }
                        $this->loader->getChooseItemForm($sender);
                        return true;
                }else{
                        $sender->sendMessage("Can't execute that Command here, please go in-game.");
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