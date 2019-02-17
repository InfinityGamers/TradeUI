<?php

declare(strict_types=1);

namespace InfinityGamers\TradeUI\UIForms;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use function json_encode;
class CustomForm {

        /** @var int */
        protected $id;
        /** @var array */
        protected $formData = [];

        /**
         * CustomForm constructor.
         */
        public function __construct(){
                $this->formData["type"] = "custom_form";
                $this->formData["content"] = [];
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
                $data = (string) $this->getEncodedFormData();
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
         * @param string $label
         */
        public function setLabel(string $label) {
                $this->formData["content"][] = [
                    "type" => "label",
                    "text" => $label,
                ];
        }

        /**
         * @param string    $toggle
         * @param bool|null $value
         */
        public function setToggle(string $toggle, bool $value = null){
                $this->formData["content"][] = [
                    "type" => "toggle",
                    "text" => $toggle,
                    "default" => $value !== null ? $value : false
                ];
        }

        /**
         * @param string   $slider
         * @param int      $min
         * @param int      $max
         * @param int|null $step
         * @param int|null $default
         */
        public function setSlider(string $slider, int $min, int $max, int $step = null, int $default = null){
                $this->formData["content"][] = [
                    "type" => "slider",
                    "text" => $slider,
                    "min" => $min <= 0 ? 1 : $min,
                    "max" => $max > 0 ? $max : 1,
                    "step" => $step !== null ? $step : 1,
                    "default" => $default !== null ? $default : 1
                ];
        }

        /**
         * @param string   $dropdown
         * @param array    $options
         * @param int|null $default
         */
        public function setDropdown(string $dropdown, array $options, int $default = null){
                $this->formData["content"][] = [
                    "type" => "dropdown",
                    "text" => $dropdown,
                    "options" => $options,
                    "default" => $default !== null ? $default : 1
                ];
        }

        /**
         * @param string      $input
         * @param string      $placeholder
         * @param string|null $default
         */
        public function setInput(string $input, string $placeholder = '', string $default = null){
                $this->formData["content"][] = [
                    "type" => "input",
                    "text" => $input,
                    "placeholder" => $placeholder,
                    "default " => $default !== null ? $default : ''
                ];
        }
}