<?php
namespace InfinityGamers\TradeUI\FormHandler;
use pocketmine\Player;
class FormRequest{
        /** @var FormHandler */
        protected $formHandler;
        /** @var Player */
        protected $player;
        /** @var mixed */
        protected $extraData;

        /**
         * FormRequest constructor.
         *
         * @param FormHandler $handler
         * @param Player      $player
         * @param null        $extraData
         */
        public function __construct(FormHandler $handler, Player $player, $extraData = null){
                $this->formHandler = $handler;
                $this->player = $player;
                $this->extraData = $extraData;

                $this->formHandler->setData($extraData);
        }

        /**
         * @param $formData
         */
        public function process($formData){
                $this->formHandler->handle($this->player, $formData);
                $this->formHandler->after($this->player);
        }

        /**
         * @return FormHandler
         */
        public function getFormHandler(): FormHandler{
                return $this->formHandler;
        }

        /**
         * @param FormHandler $formHandler
         */
        public function setFormHandler(FormHandler $formHandler): void{
                $this->formHandler = $formHandler;
        }

        /**
         * @return Player
         */
        public function getPlayer(): Player{
                return $this->player;
        }
}