<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
use function count;
class MyOffersForm extends FormHandler{
        public function send(Player $player){
                $username = $player->getName();
                $s = $this->main->fetchFromUsername($username);
                if(count($s) != 0){
                        $form = new SimpleForm();
                        $form->setId($this->formId);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lYOUR OFFERS&r&k&e|"));
                        $this->main->cache[$username]["ids"] = [];
                        foreach($s as $r){
                                $item = Item::jsonDeserialize(json_decode($r['item'], true));
                                $this->main->cache[$username]["ids"][] = $r['id'];
                                $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getCount()}&d)&e&k|"), "http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
                        }
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_offers')));
                }
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                $id = $this->main->cache[$username]["ids"][$formData];
                $this->main->resetCache($username);
                $this->main->cache[$username]["id"] = $id;
                $this->main->formManager->sendForm($this->main->CONFIRM_DELETE_OFFER_UI_ID, $player, $id);
        }
}