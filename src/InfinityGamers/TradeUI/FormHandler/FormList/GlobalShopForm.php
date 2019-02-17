<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
use function count;
class GlobalShopForm extends FormHandler{
        private $sent = false;

        public function send(Player $player){
                $username = $player->getName();
                $from = $this->getData();
                $s = $this->main->fetchItems($player);
                if($s !== null){
                        if(count($s) != 0){
                                $this->main->cache[$username]["buying_from"] = $from;
                                $form = new SimpleForm();
                                $form->setId($this->formId);
                                $pg = $this->main->getPaginationArray($player);
                                $items = $pg[0];
                                $page = $pg[1];
                                $max = $pg[2];
                                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lPUBLIC SHOP&r&k&e|&r"));
                                $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d← Last&e&k|"));
                                $form->setContent(RandomUtils::colorMessage(
                                    "            &l&dTotal of &7$items &ditems\n" .
                                    "          &l&e- &r&l&dPage &7$page &dof &7$max&e -"
                                ));
                                $i = 1;
                                $this->main->cache[$username]["ids"] = [];
                                foreach($s as $r){
                                        $item = Item::jsonDeserialize(json_decode($r['item'], true));
                                        if(!isset($this->cache["ids"])){
                                                $this->main->cache[$username]["ids"][$i++] = $r['id'];
                                        }
                                        $form->setButton(RandomUtils::colorMessage(
                                            "&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getCount()}&d)&e&k|&r\n" .
                                            "&8Seller - &6{$r['username']}&r\n" .
                                            "&8Price - &6{$r['price']}&r\n"
                                        ), "http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
                                }
                                $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dNext →&e&k|"));
                                $form->send($player);
                                $this->sent = true;
                        }else{
                                $player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->main->getMessage('no_items_public'))));
                                $this->sent = false;
                        }
                }
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                $ids = $this->main->cache[$username]["ids"];
                if(!isset($ids[$formData])){
                        $this->main->queue[$username]['type'] = $formData === 0 ? 0 : 1;
                        if($formData === 0){
                                $this->main->queue[$username]['type'] = 0;
                        }else{
                                $this->main->queue[$username]['type'] = 1;
                        }
                        $this->send($player);
                        if(!$this->sent){
                                $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_more_items')));
                        }
                        return;
                }

                $id = $ids[$formData];
                $this->main->formManager->sendForm($this->main->CONFIRM_PURCHASE_UI_ID, $player, $id);
                $this->main->resetCache($username);
                $this->main->cache[$username]["id"] = $id;
        }
}