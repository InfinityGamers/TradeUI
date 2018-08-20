<?php

namespace TradeUI;

use onebone\economyapi\EconomyAPI;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use TradeUI\Commands\Ah;
use TradeUI\Commands\Cart;
use TradeUI\Commands\MyOffers;
//use TradeUI\Commands\Sell;
use TradeUI\UIForms\CustomForm;
use TradeUI\UIForms\SimpleForm;
use TradeUI\EconomyProvider\EconomyProvider;
use TradeUI\EconomyProvider\EconomySProvider;
use TradeUI\Utils\RandomUtils;

class TradeUI extends PluginBase{

        /** @var array */
        protected $cache = [];
        /** @var array */
        protected $queue = [];

        /** @var string[] */
        protected $messages = [];

        /** @var EconomyProvider */
        public $economyProvider;

        /**
         *
         * @var int $SELL_UI_ID
         * @var int $SHOP_UI_ID
         *
         */
        public $BUY_OR_SELL_UI_ID = 0, $CHOOSE_ITEM_UI_ID = 0, $SELL_UI_ID = 0, $SHOP_UI_ID = 0, $CONFIRM_PURCHASE_UI_ID = 0, $MY_OFFERS_UI_ID = 0, $CONFIRM_DELETE_OFFER_UI_ID = 0;

        /** @var \SQLite3 */
        private $db;

	public function onEnable(){
	        $this->BUY_OR_SELL_UI_ID = mt_rand(111111, 999999);
	        $this->CHOOSE_ITEM_UI_ID = $this->BUY_OR_SELL_UI_ID + 10;
	        $this->SELL_UI_ID = $this->CHOOSE_ITEM_UI_ID + 10;
	        $this->SHOP_UI_ID = $this->SELL_UI_ID + 10;
	        $this->CONFIRM_PURCHASE_UI_ID = $this->SHOP_UI_ID + 10;
	        $this->MY_OFFERS_UI_ID = $this->CONFIRM_PURCHASE_UI_ID + 10;
	        $this->CONFIRM_DELETE_OFFER_UI_ID = $this->MY_OFFERS_UI_ID + 10;
	        $this->db = new \SQLite3($this->getDataFolder() . "auctionHouse.sqlite");
	        $this->db->exec("CREATE TABLE IF NOT EXISTS history (username VARCHAR, trade VARCHAR, itemId INT, itemMeta INT, price INT, amount INT, id INT, enchants VARCHAR)"); //username, traded item name, item id, item damage, price, amount, unique sell id, enchantments
                $this->registerCommands();
                $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
                if(!file_exists($this->getDataFolder() . "cache.data")){
                        $this->saveCache();
                }

                $economyAPI = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');

                if($economyAPI instanceof EconomyAPI){
                        $this->setEconomyProvider(new EconomySProvider($economyAPI));
                }else{
                        $this->getLogger()->notice('EconomyAPI is not enabled. Please enabled it and try again.');
                        $this->getServer()->getPluginManager()->disablePlugin($this);

                        return;
                }

                $this->saveResource('messages.yml');

                $this->messages = (new Config($this->getDataFolder() . 'messages.yml'))->getAll();

                /*$v = [];

                $q = $this->db->query("SELECT id FROM history ORDER BY id ASC");
                while($qv = $q->fetchArray(SQLITE3_ASSOC)){
                        $v[] = $qv['id'];
                }

                var_dump($v);*/

                $this->loadCache();
	}

        public function registerCommands(){
                $commands = [
                    //"sell" => new Sell($this),
                    "ah" => new Ah($this),
                    "cart" => new Cart($this),
                    "myoffers" => new MyOffers($this)
                ];
                foreach($commands as $prefix => $command){
                        $this->getServer()->getCommandMap()->register($prefix, $command);
                }
        }


        /**
         *
         * @param EconomyProvider $provider
         *
         */
	public function setEconomyProvider(EconomyProvider $provider){
	        $this->economyProvider = $provider;
        }

        /**
         *
         * @param string $message
         *
         * @return string
         *
         */
        public function getMessage(string $message){
	      return $this->messages[$message];
        }

        /**
         *
         * Saves cached data for later use
         *
         */
        public function saveCache() {
	        file_put_contents($this->getDataFolder() . "cache.data", gzencode(json_encode($this->cache)));
        }


        /**
         *
         * Loads cached data for later use
         *
         */
        public function loadCache() {
	        $this->cache = json_decode(gzdecode(file_get_contents($this->getDataFolder() . "cache.data")), true);
        }


        /**
         *
         * @param string $player
         *
         */
        public function resetCache(string $player){
                if(isset($this->cache[strtolower($player)])){
                        if(isset($this->queue[$player])){
                                unset($this->queue[$player]);
                        }
                        $this->cache[strtolower($player)] = [];
                        $this->saveCache();
                }
        }

        /**
         *
         * @param string $player
         *
         * @return array
         *
         */
        public function getCache(string $player): array{
                if(isset($this->cache[strtolower($player)])){
                        return $this->cache[strtolower($player)];
                }else{
                        $this->resetCache($player);
                        return [];
                }
        }

        /**
         *
         * @param Player $player

         *
         */
        public function savePlayerItemNames(Player $player){
                $username = strtolower($player->getName());
                $items = $player->getInventory()->getContents();
                $this->cache[$username]["items"] = [];

                foreach($items as $key => $item){
                        $this->cache[$username]["items"][] = $key;
                }
        }

        /**
         *
         * @param Player $player
         *
         */
        public function getBuyOrSellForm(Player $player) {
                $username = strtolower($player->getName());
                $form = new SimpleForm();
                $form->setId($this->BUY_OR_SELL_UI_ID);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY OR SELL?&r&k&e|"));
                $content = "";
                $content .= RandomUtils::colorMessage("&l&e===========================\n");
                $content .= RandomUtils::colorMessage("&r&dHey there, {$username}!\n\n");
                $content .= RandomUtils::colorMessage("&r&dIf you want to buy items from the\n");
                $content .= RandomUtils::colorMessage("&r&dother players, click 'BUY'!\n\n");
                $content .= RandomUtils::colorMessage("&r&dIf you want to sell your own items\n");
                $content .= RandomUtils::colorMessage("&r&dclick 'SELL'!\n");
                $content .= RandomUtils::colorMessage("&l&e===========================\n");
                $form->setContent($content);
                $form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lBUY&r&k&e|"));
                $form->setButton(RandomUtils::colorMessage("&e&k|&r&d&lSELL&r&k&e|"));
                $form->send($player);
        }

        /**
         *
         * @param Player $player
         *
         */
        public function getChooseItemForm(Player $player) {
                $form = new SimpleForm();
                $form->setId($this->CHOOSE_ITEM_UI_ID);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lCHOOSE AN ITEM TO SELL&r&k&e|"));
                $this->savePlayerItemNames($player);
                $items = $player->getInventory()->getContents();
                foreach($items as $key => $item){
                        $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$item->getName()} &d(&8x{$item->getCount()}&d)&e&k|"),
                            "http://permat.comli.com/items/{$item->getId()}-{$item->getDamage()}.png");
                }
                $form->send($player);
        }

        /**
         *
         * @param Player $player
         * @param Item   $item
         *
         */
        public function getSellForm(Player $player, Item $item) {
                $form = new CustomForm();
                $form->setId($this->SELL_UI_ID);
                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lSELL {$item->getName()}&r&k&e|"));
                $form->setInput(RandomUtils::colorMessage("&e&k|&r&d&lPRICE&r&k&e|"), "the price you're selling this item for.");
                $form->setSlider(RandomUtils::colorMessage("&d&lAMOUNT&r&e"), 1, $item->getCount(), 1, 1);
                $form->send($player);
        }

        /**
         *
         * @param Player      $player
         * @param string|null $from
         *
         * @return bool
         *
         */
        public function getShopForm(Player $player, string $from = null) {
                $username = strtolower($player->getName());
                if($from !== null){
                        $s = $this->fetchFromUsername(strtolower($from));
                        if(count($s) != 0){
                                $form = new SimpleForm();
                                $form->setId($this->SHOP_UI_ID);
                                $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&l{$from}'s SHOP&r&k&e|"));
                                foreach($s as $r) {
                                        $this->cache[$username]["ids"][] = $r['id'];
                                        $form->setButton(
                                            RandomUtils::colorMessage(
                                                "&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|&r\n" .
                                                "&8Price - &6{$r['price']}&r"
                                            ), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
                                }
                                $form->send($player);
                        }else{
                                $player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->getMessage('no_items_player'))));
                        }
                }else{
                        $s = $this->fetchItems($player);

                        if($s !== null){
                                if(count($s) != 0){
                                        $this->cache[$username]["buying_from"] = $from;
                                        $form = new SimpleForm();
                                        $form->setId($this->SHOP_UI_ID);
                                        $pg = $this->getPaginationArray($player);
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
                                        $this->cache[$username]["ids"] = [];
                                        foreach($s as $r){
                                                if(!isset($this->cache["ids"])){
                                                        $this->cache[$username]["ids"][$i++] = $r['id'];
                                                }
                                                $form->setButton(RandomUtils::colorMessage(
                                                    "&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|&r\n" .
                                                    "&8Seller - &6{$r['username']}&r\n" .
                                                    "&8Price - &6{$r['price']}&r\n"
                                                ), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
                                        }
                                        $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&dNext →&e&k|"));
                                        $form->send($player);
                                }else{
                                        $player->sendMessage(RandomUtils::colorMessage(str_replace('@from', $from, $this->getMessage('no_items_public'))));
                                }
                        }else{
                                return false;
                        }
                }

                return true;
        }

        /**
         *
         * @param Player $player
         *
         */
        public function getMyOffersUI(Player $player){
                $username = strtolower($player->getName());
                $s = $this->fetchFromUsername($username);
                if(count($s) != 0){
                        $form = new SimpleForm();
                        $form->setId($this->MY_OFFERS_UI_ID);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lYOUR OFFERS&r&k&e|"));
                        $this->cache[$username]["ids"] = [];
                        foreach($s as $r){
                                $this->cache[$username]["ids"][] = $r['id'];
                                $form->setButton(RandomUtils::colorMessage("&l&k&e|&r&l&d{$r['trade']} &d(&8x{$r['amount']}&d)&e&k|"), "http://permat.comli.com/items/{$r['itemId']}-{$r['itemMeta']}.png");
                        }
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_offers')));
                }
        }


        /**
         *
         * @param Player $player
         * @param int    $id
         *
         */
        public function getConfirmPurchaseForm(Player $player, int $id) {
                $dat = $this->fetchFromId($id);
                if(count($dat) > 0){
                        $form = new SimpleForm();
                        $form->setId($this->CONFIRM_PURCHASE_UI_ID);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO BUY THIS ITEM?&r&k&e|"));
                        $content = "";
                        $content .= RandomUtils::colorMessage("&l&e===========================&r\n");
                        $content .= RandomUtils::colorMessage("&dSeller: &7" . $dat['username'] . "\n");
                        $content .= RandomUtils::colorMessage("&dItem Name: &7" . $dat['trade'] . " &r&5(" . Item::get($dat['itemId'])->getName() . ")\n");
                        $content .= RandomUtils::colorMessage("&dPrice: &7" . $dat['price'] . "\n");
                        $content .= RandomUtils::colorMessage("&dAmount: &7" . $dat['amount'] . "\n");
                        $content .= RandomUtils::colorMessage("&dEnchantments: &7" . $this->formatEnchantmentIdsAsName($dat['enchants']) . "\n");
                        $content .= RandomUtils::colorMessage("&dMarket ID: &7" . $dat['id'] . "\n");
                        $content .= RandomUtils::colorMessage("&l&e===========================\n");
                        $form->setContent($content);
                        $form->setButton(RandomUtils::colorMessage("&a&lYES"));
                        $form->setButton(RandomUtils::colorMessage("&c&lNO"));
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_longer_available')));
                }
        }

        /**
         *
         * @param Player $player
         * @param int    $id
         *
         */
        public function getConfirmDeleteForm(Player $player, int $id) {
                $dat = $this->fetchFromId($id);
                if(count($dat) > 0){
                        $form = new SimpleForm();
                        $form->setId($this->CONFIRM_DELETE_OFFER_UI_ID);
                        $form->setTitle(RandomUtils::colorMessage("&e&k|&r&5&lDO YOU WANT TO REMOVE THIS ITEM?&r&k&e|"));
                        $content = "";
                        $content .= RandomUtils::colorMessage("&l&e===========================&r\n");
                        $content .= RandomUtils::colorMessage("&4&lNOTE: &7all your items will be returned.&r\n");
                        $content .= RandomUtils::colorMessage("&dItem Name: &7" . $dat['trade'] . " &r&5(" . Item::get($dat['itemId'])->getName() . ")\n");
                        $content .= RandomUtils::colorMessage("&dPrice: &7" . $dat['price'] . "\n");
                        $content .= RandomUtils::colorMessage("&dAmount: &7" . $dat['amount'] . "\n");
                        $content .= RandomUtils::colorMessage("&dEnchantments: &7" . $this->formatEnchantmentIdsAsName($dat['enchants']) . "\n");
                        $content .= RandomUtils::colorMessage("&dMarket ID: &7" . $dat['id'] . "\n");
                        $content .= RandomUtils::colorMessage("&l&e===========================\n");
                        $form->setContent($content);
                        $form->setButton(RandomUtils::colorMessage("&a&lYES"));
                        $form->setButton(RandomUtils::colorMessage("&c&lNO"));
                        $form->send($player);
                }else{
                        $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_longer_available')));
                }
        }

        /**
         *
         * @param Player $player
         * @param Item   $item
         *
         * @return int
         *
         */
        protected function getCountFromItem(Player $player, Item $item): int{
                $count = 0;
                foreach($player->getInventory()->all($item) as $slot => $i){
                        $count += $i->getCount();
                }
                return $count;
        }

        /**
         *
         * @param string $enchants
         *
         * @return string
         *
         */
        protected function formatEnchantmentIdsAsName(string $enchants): string{
                if(strlen($enchants) === 0){
                        return 'none';
                }

                $return = [];
                $enchants = $this->parseEnchantments($enchants);

                foreach($enchants as $enchant){
                        $return[] = $enchant->getType()->getName() . " (" . $enchant->getLevel() . ")";
                }

                return implode(", ", $return);
        }

        /**
         *
         * @param array|string $enchants
         *
         * @return EnchantmentInstance[]
         *
         */
        protected function parseEnchantments($enchants): array{
                /** @var EnchantmentInstance[] $return */
                $return = [];
                if(strlen($enchants) === 0){
                        return [];
                }
                if(is_string($enchants)){
                        $enchants = explode(';', $enchants);
                }
                foreach($enchants as $enchant){
                        $parts = explode(':', $enchant);
                        $enchant = Enchantment::getEnchantment((int)$parts[0]);
                        if($enchant !== null){
                                $return[] = new EnchantmentInstance($enchant, (int)$parts[1]);
                        }
                }
                return $return;
        }

        /**
         *
         * I KNOW THIS IS REALLY MESSY, BUT FORMS DON'T HELP AT ALL
         *
         * @param Player $player
         * @param mixed  $formData
         * @param int    $formId
         *
         */
        public function handleFormResponse(Player $player, $formData, int $formId){
                $username = strtolower($player->getName());
                switch($formId) {
                        case $this->BUY_OR_SELL_UI_ID: {
                                if($formData === 0){
                                        unset($this->queue[$username]);
                                        $this->getShopForm($player);
                                }else{
                                        if(count($player->getInventory()->getContents()) === 0){
                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_empty')));
                                                return;
                                        }
                                        $this->getChooseItemForm($player);
                                }
                        }
                                break;
                        case $this->CHOOSE_ITEM_UI_ID: {
                                $index = $this->getCache($username)["items"][$formData];
                                $item = $player->getInventory()->getItem($index);
                                $this->getSellForm($player, $item);
                                $this->resetCache($player);
                                $this->cache[$username]["item"] = $index;
                        }
                                break;
                        case $this->SHOP_UI_ID: {
                                        $ids = $this->cache[$username]["ids"];
                                        if(!isset($ids[$formData])){
                                                if($formData === 0){
                                                        $this->queue[$username]['type'] = 0;
                                                        if(!$this->getShopForm($player)){
                                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_more_items')));
                                                        }
                                                }else{
                                                        $this->queue[$username]['type'] = 1;
                                                        if(!$this->getShopForm($player)){
                                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_more_items')));
                                                        }
                                                }
                                                return;
                                        }

                                        $id = $ids[$formData];
                                        $this->getConfirmPurchaseForm($player, $id);
                                        $this->resetCache($username);
                                        $this->cache[$username]["id"] = $id;
                                }
                                break;
                        case $this->SELL_UI_ID: {
                                        if($formData[0] !== (string)(int)($formData[0]) or $formData[0] === null){
                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('enter_valid_price')));
                                                return;
                                        }
                                        $item = $player->getInventory()->getItem($this->getCache($username)["item"]);
                                        $c = $this->getCountFromItem($player, $item);
                                        if($c < $formData[1]){
                                                $player->sendMessage(RandomUtils::colorMessage(str_replace(['@chose', '@have'], [$formData[1], $c], $this->getMessage('not_enough_to_sell'))));
                                                return;
                                        }
                                        $item->setCount($formData[1]);
                                        $player->getInventory()->removeItem($item);
                                        $enchants = [];
                                        foreach($item->getEnchantments() as $enchantment){
                                                $enchants[] = $enchantment->getType()->getId() . ':' . $enchantment->getLevel();
                                        }
                                        $this->insert($username, $item->getName(), $item->getId(), $item->getDamage(), (int)$formData[0], $formData[1], implode(';', $enchants));
                                        $this->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $formData[0], $formData[1]], $this->getMessage('selling_item'))));
                                        //$this->getServer()->broadcastMessage(TextFormat::GREEN . "To view, type: " . TextFormat::RED . "/ah " . $username);
                                }
                                break;
                        case $this->CONFIRM_PURCHASE_UI_ID: {
                                        if($formData === 0){
                                                $dat = $this->fetchFromId($this->cache[$username]["id"]);
                                                if(count($dat) > 0){
                                                        $item = Item::get($dat['itemId'], $dat['itemMeta']);
                                                        $item->setCount($dat['amount']);
                                                        foreach($this->parseEnchantments($dat['enchants']) as $enchantment){
                                                                $item->addEnchantment($enchantment);
                                                        }
                                                        if(strtolower($item->getName()) !== strtolower($dat['trade'])){
                                                                $item->setCustomName($dat['trade']);
                                                        }
                                                        if($player->getInventory()->canAddItem($item)){
                                                                if($this->economyProvider->getMoney($player) < $dat['price']){
                                                                        $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_sufficient_funds')));
                                                                        return;
                                                                }
                                                                $this->economyProvider->addMoney($dat['username'], $dat['price']);
                                                                $this->economyProvider->subtractMoney($player, $dat['price']);
                                                                $player->getInventory()->addItem($item);
                                                                $this->deleteFromId($dat['id']);
                                                                $this->resetCache($username);
                                                                $pl = $this->getServer()->getPlayer($dat['username']);
                                                                if($pl !== null){
                                                                        $this->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $dat['price'], $dat['amount']], $this->getMessage('bought_item'))));
                                                                }else{
                                                                        $this->cache[$dat['username']]['purchasesMessage'] .= TextFormat::YELLOW . $username . TextFormat::GREEN . " bought " . $item->getName() . " (x" . $dat['amount'] . ") from you for $" . $dat['price'] . "\n";
                                                                        $this->getServer()->broadcastMessage(TextFormat::GREEN . $username . " has bought " . $item->getName() . " (x" . $dat['amount'] . ") from " . $dat['username'] . " for $" . $dat['price']);
                                                                }
                                                        }else{
                                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('inventory_full')));
                                                        }
                                                }else{
                                                        $player->sendMessage(RandomUtils::colorMessage($this->getMessage('no_longer_available')));
                                                }
                                        }
                                }
                                break;
                        case $this->MY_OFFERS_UI_ID: {
                                $id = $this->cache[$username]["ids"][$formData];
                                $this->getConfirmDeleteForm($player, $id);
                                $this->resetCache($username);
                                $this->cache[$username]["id"] = $id;
                        }
                                break;
                        case $this->CONFIRM_DELETE_OFFER_UI_ID: {
                                if($formData === 0){
                                        $id = $this->cache[$username]["id"];
                                        $dat = $this->fetchFromId($id);
                                        $item = Item::get($dat['itemId'], $dat['itemMeta']);
                                        $item->setCount($dat['amount']);
                                        foreach($this->parseEnchantments($dat['enchants']) as $enchantment){
                                                $item->addEnchantment($enchantment);
                                        }
                                        if(strtolower($item->getName()) !== strtolower($dat['trade'])){
                                                $item->setCustomName($dat['trade']);
                                        }
                                        $this->deleteFromId($id);
                                        if($player->getInventory()->canAddItem($item)){
                                                $player->getInventory()->addItem($item);
                                                $this->getServer()->broadcastMessage(RandomUtils::colorMessage(str_replace(["@player", "@item", "@price", "@amount"], [$username, $item->getName(), $dat['price'], $dat['amount']], $this->getMessage('deleted_item'))));
                                        }else{
                                                $player->sendMessage(RandomUtils::colorMessage($this->getMessage('inventory_full_2')));
                                        }
                                }
                        }
                                break;
                }
        }

        /**
         *
         * @param Player $player
         *
         * @return array
         *
         */
        protected function getPaginationArray(Player $player): array {
                $username = strtolower($player->getName());
                $tc = $this->getRowCount();
                $c = 0;

                for($i = 0; $i < $tc; $i += 10){
                        $c++;
                }

                $p = $this->queue[$username]['page'];

                return [$tc, $p, $c]; //amount of items, current page, amount of total pages
        }

        /**
         *
         * Fetches what market items player will look at next
         *
         * @param Player $player
         *
         * @return null|array
         *
         */
        protected function fetchItems(Player $player){
                $username = strtolower($player->getName());
                if(isset($this->queue[$username])){
                        $s = $this->queue[$username];
                        $type = $this->queue[$username]['type'];
                        switch($type){
                                case 0:
                                        $this->queue[$username]['skip'] -= 10;
                                        $f = $this->fetchLastFromMarketID($s['last'], $this->queue[$username]['skip']);
                                        if(!empty($f)){
                                                $this->queue[$username]['page']--;
                                                $this->queue[$username]['last'] = $f[0]['id'];
                                                $this->queue[$username]['next'] = end($f)['id'];
                                                return $f;
                                        }
                                        unset($this->queue[$username]);
                                        return null;
                                case 1:
                                        $this->queue[$username]['skip'] += 10;
                                        $f = $this->fetchNextFromMarketID($s['next']);
                                        if(!empty($f)){
                                                $this->queue[$username]['page']++;
                                                $this->queue[$username]['last'] = $f[0]['id'];
                                                $this->queue[$username]['next'] = end($f)['id'];
                                                return $f;
                                        }
                                        unset($this->queue[$username]);
                                        return null;
                        }
                }else{
                        $f = $this->fetchNextFromMarketID(0);
                        if(!empty($f)){
                                $this->queue[$username]['skip'] = 0;
                                $this->queue[$username]['page'] = 1;
                                $this->queue[$username]['last'] = $f[0]['id'];
                                $this->queue[$username]['next'] = count($f) > 1 ? end($f)['id'] : $f[0]['id'];
                                return $f;
                        }
                }
                unset($this->queue[$username]);
                return null;
        }

        /**
         *
         * @return int
         *
         */
        public function getRowCount(): int{
                $rows = $this->db->query("SELECT COUNT(*) as count FROM history");
                $row = $rows->fetchArray();
                $numRows = $row['count'];

                return $numRows;
        }

        /**
         *
         * @param string $from
         *
         * @return array
         *
         */
        public function fetchFromUsername(string $from): array {
                $out = [];
                $q = $this->db->query("SELECT * FROM history WHERE username = '$from'");
                while($row = $q->fetchArray(SQLITE3_ASSOC)){
                        $out[] = $row;
                }
                return $out;
        }

        /**
         *
         * @param string $id
         *
         * @return array
         *
         */
        public function fetchFromId(string $id): array {
                $q = $this->db->query("SELECT * FROM history WHERE id = '$id'");
                while($row = $q->fetchArray(SQLITE3_ASSOC)){
                        return $row;
                }
                return [];
        }

        /**
         *
         * @return array
         *
         */
        public function fetchAll(): array {
                $out = [];
                $q = $this->db->query("SELECT * FROM history");
                while($row = $q->fetchArray(SQLITE3_ASSOC)){
                        $out[] = $row;
                }
                return $out;
        }

        /**
         *
         * @param int $marketId
         * @param int $skip
         *
         * @return array
         *
         */
        public function fetchLastFromMarketID(int $marketId, int $skip): array {
                $out = [];
                $q = $this->db->query("SELECT * FROM history WHERE id < $marketId LIMIT 10 OFFSET $skip");
                while($row = $q->fetchArray(SQLITE3_ASSOC)){
                        $out[] = $row;
                }
                return $out;
        }

        /**
         *
         * @param int $marketId
         *
         * @return array
         *
         */
        public function fetchNextFromMarketID(int $marketId): array {
                $out = [];
                $q = $this->db->query("SELECT * FROM history WHERE id > $marketId LIMIT 10");
                while($row = $q->fetchArray(SQLITE3_ASSOC)){
                        $out[] = $row;
                }
                return $out;
        }

        /**
         *
         * @param string $id
         *
         */
        public function deleteFromId(string $id) {
                $this->db->query("DELETE FROM history WHERE id = '$id'");
        }


        /**
         *
         * @param string $username
         * @param string $item
         * @param int    $itemId
         * @param int    $itemMeta
         * @param int    $price
         * @param int    $amount
         * @param string $enchants
         * @param int    $id
         *
         */
        public function insert(string $username, string $item, int $itemId, int $itemMeta, int $price, int $amount, string $enchants, int $id = null) {
                $q = $this->db->prepare("INSERT INTO history (username, trade, itemId, itemMeta, price, amount, id, enchants) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if($id === null){
                        $id = time();
                }
                $q->bindParam(1, $username);
                $q->bindParam(2, $item);
                $q->bindParam(3, $itemId);
                $q->bindParam(4, $itemMeta);
                $q->bindParam(5, $price);
                $q->bindParam(6, $amount);
                $q->bindParam(7, $id);
                $q->bindParam(8, $enchants);
                $q->execute();
        }

}