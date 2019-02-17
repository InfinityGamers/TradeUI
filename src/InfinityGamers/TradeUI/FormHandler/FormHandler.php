<?php
namespace InfinityGamers\TradeUI\FormHandler;
use pocketmine\Player;
use InfinityGamers\TradeUI\TradeUI;
abstract class FormHandler {
        /** @var TradeUI */
        protected $main;
        /** @var int */
        protected $formId;

        /** @var mixed */
        protected $data; // used as extra data for forms

        public function __construct(TradeUI $tradeUI, int $formId){
                $this->main = $tradeUI;
                $this->formId = $formId;
        }

        /**
         * @return mixed
         */
        public function getData(){
                return $this->data;
        }

        public function setData($data): void{
                $this->data = $data;
        }

        public function getFormId(): int{
                return $this->formId;
        }

        public function after(Player $player){
        }

        abstract public function send(Player $player);
        abstract public function handle(Player $player, $formData);
}