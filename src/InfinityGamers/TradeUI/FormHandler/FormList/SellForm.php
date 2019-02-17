<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\item\Item;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\CustomForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;

class SellForm extends FormHandler{
        public function send(Player $player){
                /** @var Item $item */
                $item = $this->getData();

                $form = new CustomForm();
                $form->setId($this->formId);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lSELL {$item->getName()}&r&k&e|"));
                $form->setInput(RandomUtils::colorMessage("&e&k|&r&d&lPRICE&r&k&e|"), "the price you're selling this item for.");
                $form->setSlider(RandomUtils::colorMessage("&d&lAMOUNT&r&e"), 1, $item->getCount(), 1, 1);
                $form->send($player);
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                if($formData[0] !== (string)(int)($formData[0]) or $formData[0] === null){
                        $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('enter_valid_price')));
                        return;
                }
                $item = $player->getInventory()->getItem($this->main->cache[$username]["item"]);
                $c = $this->main->getCountFromItem($player, $item);
                if($c < $formData[1]){
                        $player->sendMessage(RandomUtils::colorMessage(str_replace(['@chose', '@have'], [$formData[1], $c], $this->main->getMessage('not_enough_to_sell'))));
                        return;
                }
                $item->setCount($formData[1]);
                $player->getInventory()->removeItem($item);
                $this->main->insert($item, $player, (int)$formData[0]);
                $this->main->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $formData[0], $formData[1]], $this->main->getMessage('selling_item'))));
        }
}