<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
use function count;
class PlayerShopForm extends FormHandler{
        public function send(Player $player){
                $from = $this->getData();
                $s = $this->main->fetchFromUsername($from);
                $username = $player->getName();
                if(count($s) != 0){
                        $form = new SimpleForm();
                        $form->setId($this->formId);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&l{$from}'s SHOP&r&k&e|"));
                        foreach($s as $r) {
                                $item = Item::jsonDeserialize(json_decode($r['item'], true));
                                $this->main->cache[$username]["ids"][] = $r['id'];
                                $form->setButton(
                                    RandomUtils::colorMessage(
                                        "&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getId()}&d)&e&k|&r\n" .
                                        "&8Price - &6{$r['price']}&r"
                                    ), "http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
                        }
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->main->getMessage('no_items_player'))));
                }
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                $ids = $this->main->cache[$username]["ids"];
                $id = $ids[$formData];
                $this->main->formManager->sendForm($this->main->CONFIRM_PURCHASE_UI_ID, $player, $id);
                $this->main->resetCache($username);
                $this->main->cache[$username]["id"] = $id;
        }
}