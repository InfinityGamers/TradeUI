<?php
namespace InfinityGamers\TradeUI\FormHandler\FormList;
use pocketmine\Player;
use InfinityGamers\TradeUI\FormHandler\FormHandler;
use InfinityGamers\TradeUI\UIForms\SimpleForm;
use InfinityGamers\TradeUI\Utils\RandomUtils;
use function count;
class MainForm extends FormHandler{
        public function send(Player $player){
                $username = strtolower($player->getName());
                $form = new SimpleForm();
                $form->setId($this->formId);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY OR SELL?&r&k&e|"));
                $content = "&l&e===========================\n";
                $content .= "&r&dHey there, {$username}!\n\n";
                $content .= "&r&dIf you want to buy items from the\n";
                $content .= "&r&dother players, click 'BUY'!\n\n";
                $content .= "&r&dIf you want to sell your own items\n";
                $content .= "&r&dclick 'SELL'!\n";
                $content .= "&l&e===========================\n";
                $form->setContent(RandomUtils::colorMessage($content));
                $form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lBUY&r&k&e|"));
                $form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lSELL&r&k&e|"));
                $form->send($player);
        }

        public function handle(Player $player, $formData){
                $username = $player->getName();
                if($formData === 0){
                        unset($this->main->queue[$username]);
                        $this->main->formManager->sendForm($this->main->GLOBAL_SHOP_UI_ID, $player);
                }else{
                        if(count($player->getInventory()->getContents()) === 0){
                                $player->sendMessage(RandomUtils::colorMessage($this->main->getMessage('no_empty')));
                                return;
                        }
                        $this->main->formManager->sendForm($this->main->CHOOSE_ITEM_UI_ID, $player);
                }
        }
}