<?php
namespace InfinityGamers\TradeUI\Utils;
use pocketmine\utils\TextFormat;
class RandomUtils{
        /**
         *
         * @param $str
         *
         * @return mixed
         *
         */
        public static function colorMessage($str){
                return TextFormat::colorize($str);
        }
}