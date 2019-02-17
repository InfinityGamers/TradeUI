<?php
namespace InfinityGamers\TradeUI;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\FormHandler\FormRequest;
use function spl_object_hash;
class FormManager{
        /** @var TradeUI */
        protected $main;

        /** @var string[] */
        protected $formHandlers = [];
        /** @var FormRequest[] */
        protected $formRequests = [];

        /**
         * FormManager constructor.
         *
         * @param TradeUI $tradeUI
         */
        public function __construct(TradeUI $tradeUI){
                $this->main = $tradeUI;
        }

        /**
         * @param int $id
         *
         * @return bool
         */
        public function handlerExists(int $id): bool{
                return isset($this->formHandlers[$id]);
        }

        /**
         * @param int    $id
         * @param string $class
         * @param bool   $force
         */
        public function registerHandler(int $id, string $class, bool $force = false){
                if(!$this->handlerExists($id) || $force){
                        $this->formHandlers[$id] = $class;
                }
        }

        /**
         * @param int $id
         *
         * @return bool
         */
        public function deregisterHandler(int $id): bool{
                if($this->handlerExists($id)){
                        unset($this->formHandlers[$id]);
                        return true;
                }
                return false;
        }

        /**
         * @param int $id
         *
         * @return null|string
         */
        public function getHandlerClass(int $id): ?string {
                return $this->handlerExists($id) ? $this->formHandlers[$id] : null;
        }

        /**
         * @param int $id
         *
         * @return null|FormHandler
         */
        public function getHandler(int $id): ?FormHandler {
                return $this->handlerExists($id) ? new $this->formHandlers[$id]($this->main, $id) : null;
        }

        /**
         * @param int    $id
         * @param Player $player
         * @param null   $extraData
         *
         * @return bool
         */
        public function sendForm(int $id, Player $player, $extraData = null): bool{
                $handler = $this->getHandler($id);
                if($handler !== null){
                        $this->putRequest(new FormRequest($handler, $player, $extraData));
                        $handler->send($player);
                        return true;
                }
                return false;
        }

        /**
         * @param Player $player
         * @param        $formData
         */
        public function handleForm(Player $player, $formData){
                $request = $this->getRequest($player);
                if($request !== null){
                        $request->process($formData);
                }
        }

        /**
         * @param Player $player
         *
         * @return bool
         */
        public function hasRequest(Player $player): bool{
                return isset($this->formRequests[spl_object_hash($player)]);
        }

        /**
         * @param Player $player
         *
         * @return null|FormRequest
         */
        public function getRequest(Player $player): ?FormRequest{
                return $this->formRequests[spl_object_hash($player)] ?? null;
        }

        /**
         * @param FormRequest $request
         */
        public function putRequest(FormRequest $request){
                $this->formRequests[spl_object_hash($request->getPlayer())] = $request;
        }

        /**
         * @param FormRequest $request
         */
        public function discardRequest(FormRequest $request){
                unset($this->formRequests[spl_object_hash($request->getPlayer())]);
        }
}