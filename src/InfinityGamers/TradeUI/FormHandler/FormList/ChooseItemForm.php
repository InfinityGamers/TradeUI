<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
class ChooseItemForm extends FormHandler{
        private $item;
        public function send(Player $player){
                $form = new SimpleForm();
                $form->setId($this->formId);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lCHOOSE AN ITEM TO SELL&r&k&e|"));
                $this->main->savePlayerItemNames($player);
                $items = $player->getInventory()->getContents();
                foreach($items as $key => $item){
                        $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getCount()}&d)&e&k|"),
                            "http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
                }
                $form->send($player);
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                $index = $this->main->getCache($username)["items"][$formData];
                $this->item = $player->getInventory()->getItem($index);
                $this->main->resetCache($player);
                $this->main->cache[$username]["item"] = $index;
        }

        public function after(Player $player){
                $this->main->formManager->sendForm($this->main->SELL_UI_ID, $player, $this->item);
        }
}