<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;

class ConfirmDeleteForm extends FormHandler{
        public function send(Player $player){
                $dat = $this->main->fetchFromId($this->getData());
                if(count($dat) > 0){
                        $form = new SimpleForm();
                        $form->setId($this->formId);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO REMOVE THIS ITEM?&r&k&e|"));
                        $item = Item::jsonDeserialize(json_decode($dat['item'], true));
                        $content = "&l&e===========================&r\n";
                        $content .= "&4&lNOTE: &7all your items will be returned.&r\n";
                        $content .= "&dItem Name: &7" . $item->getName() . " &r&5(" . $item->getVanillaName() . ")\n";
                        $content .= "&dPrice: &7" . $dat['price'] . "\n";
                        $content .= "&dAmount: &7" . $item->getCount() . "\n";
                        $content .= "&dEnchantments: &7" . $this->main->formatEnchantmentIdsAsName($item->getEnchantments()) . "\n";
                        $content .= "&dMarket ID: &7" . $dat['id'] . "\n";
                        $content .= "&l&e===========================\n";
                        $form->setContent(RandomUtils::colorMessage($content));
                        $form->setButton(RandomUtils::colorMessage("&a&lYES"));
                        $form->setButton(RandomUtils::colorMessage("&c&lNO"));
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_longer_available')));
                }
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                if($formData === 0){
                        $id = $this->main->cache[$username]["id"];
                        $dat = $this->main->fetchFromId($id);
                        $item = Item::jsonDeserialize(json_decode($dat['item'], true));
                        $this->main->deleteFromId($id);
                        if($player->getInventory()->canAddItem($item)){
                                $player->getInventory()->addItem($item);
                                $this->main->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $dat['price'], $item->getCount()], $this->main->getMessage('deleted_item'))));
                        }else{
                                $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('inventory_full_2')));
                        }
                }
        }
}