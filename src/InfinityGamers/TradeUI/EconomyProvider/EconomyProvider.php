<?php

namespace InfinityGamers\TradeUI\EconomyProvider;
interface EconomyProvider{
        /**
         * @param $player
         * @param int    $amount
         */
        public function addMoney($player, int $amount): void;
        /**
         * @param $player
         * @param int    $amount
         */
        public function subtractMoney($player, int $amount): void;

        /**
         * @param $player
         *
         * @return int
         */
        public function getMoney($player): int;
}