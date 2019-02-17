<?php
namespace InfinityGamers\TradeUI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use InfinityGamers\TradeUI\Task\OpenPurchasesMessageForm;
class EventListener implements Listener{
        /** @var TradeUI */
        protected $loader;

        protected $loaded = false;

        /**
         * EventListener constructor.
         *
         * @param TradeUI $loader
         */
        public function __construct(TradeUI $loader){
                $this->loader = $loader;
        }

        /**
         * @param DataPacketReceiveEvent $event
         */
        public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) {
                $pk = $event->getPacket();
                $player = $event->getPlayer();
                if($pk instanceof ModalFormResponsePacket) {
                        $data = json_decode($pk->formData, true);
                        if($data !== null){
                                $this->loader->formManager->handleForm($player, $data);
                        }else{
                                $this->loader->resetCache($player);
                        }
                }
        }

        /**
         * @param PlayerJoinEvent $event
         */
        public function onJoin(PlayerJoinEvent $event){
                $player = $event->getPlayer();
                $cache = $this->loader->getCache($player->getName());
                if(isset($cache['purchasesMessage'])){
                        $this->loader->getScheduler()->scheduleDelayedTask(new OpenPurchasesMessageForm($this->loader, $cache['purchasesMessage'], $player), 20);
                        $this->loader->resetCache($player);
                }
        }
}