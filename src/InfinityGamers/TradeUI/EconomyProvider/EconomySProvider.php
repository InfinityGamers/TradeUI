<?php

namespace InfinityGamers\TradeUI\EconomyProvider;

use onebone\economyapi\EconomyAPI;

class EconomySProvider implements EconomyProvider{

        /** @var EconomyAPI */
        protected $economyAPI;

        /**
         * EconomySProvider constructor.
         *
         * @param EconomyAPI $economyAPI
         */
        public function __construct(EconomyAPI $economyAPI){
                $this->economyAPI = $economyAPI;
        }

        /**
         * @param $player
         * @param int    $amount
         */
        public function addMoney($player, int $amount): void{
                $this->economyAPI->addMoney($player, $amount);
        }

        /**
         * @param $player
         * @param int    $amount
         */
        public function subtractMoney($player, int $amount): void{
                $this->economyAPI->reduceMoney($player, $amount);
        }

        /**
         * @param $player
         *
         * @return int
         */
        public function getMoney($player): int{
                return $this->economyAPI->myMoney($player);
        }
}