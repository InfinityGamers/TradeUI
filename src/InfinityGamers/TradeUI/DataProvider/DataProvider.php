<?php
namespace InfinityGamers\TradeUI\DataProvider;
use pocketmine\item\Item;
use pocketmine\Player;
interface DataProvider{
        /**
         * @return int
         */
        public function getRowCount(): int;

        /**
         * @param string $from
         *
         * @return array
         */
        public function fetchFromUsername(string $from): array;

        /**
         * @param string $id

         * @return array
         */
        public function fetchFromId(string $id): array;

        /**
         * @return array
         */
        public function fetchAll(): array;

        /**
         * @param int $marketId
         * @param int $skip
         *
         * @return array
         */
        public function fetchLastFromMarketID(int $marketId, int $skip): array;

        /**
         * @param int $marketId
         *
         * @return array
         */
        public function fetchNextFromMarketID(int $marketId): array;

        /**
         * @param string $id
         *
         * @return mixed
         */
        public function deleteFromId(string $id);

        /**
         * @param Item   $item
         * @param Player $player
         * @param int    $price
         *
         * @return mixed
         */
        public function insert(Item $item, Player $player, int $price);
}