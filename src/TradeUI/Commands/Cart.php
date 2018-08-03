<?php

namespace TradeUI\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use TradeUI\TradeUI;
use TradeUI\Utils\RandomUtils;

class Cart extends Command implements PluginIdentifiableCommand{

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
                parent::__construct("cart", "look at your latest item", "Usage: /cart", []);
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
                        $cache = $this->loader->getCache($sender->getName());
                        if(count($cache) > 0){
                                $this->loader->getConfirmPurchaseForm($sender, $cache['id']);
                        }else{
                                $sender->sendMessage(RandomUtils::colorMessage($this->loader->getMessage('no_recent_items')));
                        }
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