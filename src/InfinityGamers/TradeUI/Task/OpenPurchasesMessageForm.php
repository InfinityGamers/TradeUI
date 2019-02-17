<?php

namespace InfinityGamers\TradeUI\Task;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use InfinityGamers\TradeUI\TradeUI;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
class OpenPurchasesMessageForm extends Task{
        /** @var string */
        public $message;
        /** @var Player */
        public $player;

        /**
         * OpenPurchasesMessageForm constructor.
         *
         * @param TradeUI $loader
         * @param string                 $message
         * @param Player                 $player
         */
        public function __construct(TradeUI $loader, string $message, Player $player){
                $this->message = $message;
                $this->player = $player;
        }

        /**
         * Actions to execute when run
         *
         * @param int $currentTick
         *
         * @return void
         */
        public function onRun(int $currentTick){
                $simpleForm = new SimpleForm();
                $simpleForm->setId(0);
                $simpleForm->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lPEOPLE BOUGHT FROM YOU&r&k&e|"));
                $simpleForm->setContent($this->message);
                $simpleForm->setButton("Okay");
                $simpleForm->send($this->player);
        }
}