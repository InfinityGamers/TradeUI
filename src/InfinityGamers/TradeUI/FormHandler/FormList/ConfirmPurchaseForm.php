<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
use function count;
class ConfirmPurchaseForm extends FormHandler{
        public function send(Player $player){
                $dat = $this->main->fetchFromId($this->getData());
                if(count($dat) > 0){
                        $item = Item::jsonDeserialize(json_decode($dat['item'], true));
                        $form = new SimpleForm();
                        $form->setId($this->formId);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY THIS ITEM?&r&k&e|"));
                        $content = "&l&e===========================&r\n";
                        $content .= "&dSeller: &7" . $dat['username'] . "\n";
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
                        $dat = $this->main->fetchFromId($this->main->cache[$username]["id"]);
                        if(count($dat) > 0){
                                $item = Item::jsonDeserialize(json_decode($dat['item'], true));
                                if($player->getInventory()->canAddItem($item)){
                                        if($this->main->economyProvider->getMoney($player) < $dat['price']){
                                                $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_sufficient_funds')));
                                                return;
                                        }
                                        $this->main->economyProvider->addMoney($dat['username'], $dat['price']);
                                        $this->main->economyProvider->subtractMoney($player, $dat['price']);
                                        $player->getInventory()->addItem($item);
                                        $this->main->deleteFromId($dat['id']);
                                        $this->main->resetCache($username);
                                        $pl = $this->main->getServer()->getPlayer($dat['username']);
                                        if($pl !== null){
                                                $this->main->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $dat['price'], $item->getCount()], $this->main->getMessage('bought_item'))));
                                        }else{
                                                $this->main->cache[$dat['username']]['purchasesMessage'] .= TextFormat::YELLOW . $username . TextFormat::GREEN . " bought " . $item->getName() . " (x" . $item->getCount() . ") from you for $" . $dat['price'] . "\n";
                                                $this->main->getServer()->broadcastMessage(TextFormat::GREEN . $username . " has bought " . $item->getName() . " (x" . $item->getCount() . ") from " . $dat['username'] . " for $" . $dat['price']);
                                        }
                                        $this->main->formManager->sendForm($this->main->GLOBAL_SHOP_UI_ID, $player);
                                }else{
                                        $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('inventory_full')));
                                }
                        }else{
                                $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_longer_available')));
                        }
                }
        }
}