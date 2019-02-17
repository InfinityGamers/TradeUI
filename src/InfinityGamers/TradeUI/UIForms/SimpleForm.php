<?php

namespace InfinityGamers\TradeUI\UIForms;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use function json_encode;
class SimpleForm{

        /** @var string */
        public static $cache = [];

        /** @var int */
        protected $id;
        /** @var array */
        protected $formData = [];

        /**
         * CustomForm constructor.
         */
        public function __construct(){
                $this->formData["type"] = "form";
                $this->formData["content"] = "";
        }


        /**
         * @param int $id
         */
        public function setId(int $id){
                $this->id = $id;
        }

        /**
         * @return int
         */
        public function getId(): int {
                return $this->id;
        }

        /**
         * @param array $formData
         */
        public function setFormData(array $formData): void{
                $this->formData = $formData;
        }

        /**
         * @return array
         */
        public function getFormData(): array{
                return $this->formData;
        }

        /**
         * @return string
         */
        public function getEncodedFormData(): string{
                return json_encode($this->formData);
        }

        /**
         * @param Player $player
         */
        public function send(Player $player) {
                $data = $this->getEncodedFormData();
                $pk = new ModalFormRequestPacket();
                $pk->formData = $data;
                $pk->formId = $this->id;
                $player->sendDataPacket($pk);
        }

        /**
         * @param string $title
         */
        public function setTitle(string $title) {
                $this->formData["title"] = $title;
        }

        /**
         * @param string $text
         */
        public function setContent(string $text) {
                $this->formData["content"] = $text;
        }

        /**
         * @param string $button
         * @param string $imageURL
         */
        public function setButton(string $button, string $imageURL = null) {

                $content = ['text' => $button];

                if($imageURL !== null){
                        $content['image']['type'] = 'url';
                        $content['image']['data'] = $imageURL;
                }

                $this->formData['buttons'][] = $content;
        }
}