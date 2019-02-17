<?php

namespace InfinityGamers\TradeUI;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use InfinityGamers\TradeUI\Commands\Ah;
use InfinityGamers\TradeUI\Commands\Cart;
use InfinityGamers\TradeUI\Commands\MyOffers;
use InfinityGamers\TradeUI\DataProvider\DataProvider;
use InfinityGamers\TradeUI\DataProvider\SQLite3Provider;
use InfinityGamers\TradeUI\EconomyProvider\EconomyProvider;
use InfinityGamers\TradeUI\EconomyProvider\EconomySProvider;
use InfinityGamers\TradeUI\FormHandler\FormList\MainForm;
use InfinityGamers\TradeUI\FormHandler\FormList\ChooseItemForm;
use InfinityGamers\TradeUI\FormHandler\FormList\ConfirmDeleteForm;
use InfinityGamers\TradeUI\FormHandler\FormList\ConfirmPurchaseForm;
use InfinityGamers\TradeUI\FormHandler\FormList\GlobalShopForm;
use InfinityGamers\TradeUI\FormHandler\FormList\MyOffersForm;
use InfinityGamers\TradeUI\FormHandler\FormList\SellForm;
use function file_exists;
use function mt_rand;
use function count;
use function implode;
use function gzencode;
use function json_encode;
use function gzdecode;
use function json_decode;
use function file_put_contents;
use function file_get_contents;
use function end;
class TradeUI extends PluginBase{
        /** @var array */
        public $cache = [];
        /** @var array */
        public $queue = [];

        /** @var string[] */
        protected $messages = [];

        /** @var EconomyProvider */
        public $economyProvider;
        /** @var DataProvider */
        public $dataProvider;

        /** @var FormManager */
        public $formManager;

        public
            $MAIN_UI_ID = 0,
            $CHOOSE_ITEM_UI_ID = 0,
            $SELL_UI_ID = 0,
            $PLAYER_SHOP_UI_ID = 0,
            $GLOBAL_SHOP_UI_ID = 0,
            $CONFIRM_PURCHASE_UI_ID = 0,
            $MY_OFFERS_UI_ID = 0,
            $CONFIRM_DELETE_OFFER_UI_ID = 0;

        /** @var \SQLite3 */
        private $db;

	public function onEnable(){
	        $this->db = new \SQLite3($this->getDataFolder() . "auctionHouse.sqlite");

                if(!file_exists($this->getDataFolder() . "cache.data")){
                        $this->saveCache();
                }
                $economyAPI = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
                if(!($economyAPI instanceof EconomyAPI)){
                        $this->getLogger()->notice('EconomyAPI is not enabled. Please enabled it and try again.');
                        $this->getServer()->getPluginManager()->disablePlugin($this);
                        return;
                }
                $this->setEconomyProvider(new EconomySProvider($economyAPI));
                $this->formManager = new FormManager($this);

                $this->saveResource('config.yml');
                $this->saveResource('messages.yml');
                $this->messages = (new Config($this->getDataFolder() . 'messages.yml'))->getAll();

                $this->initDataProvider();

                $this->loadCache();
                $this->setFormIds();
                $this->registerCommands();
                $this->registerFormHandlers();
                $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

        public function registerCommands(){
                $commands = [new Ah($this), new Cart($this), new MyOffers($this)];
                foreach($commands as $prefix => $command){
                        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), $command);
                }
        }

        public function initDataProvider(){
	        switch(strtolower($this->getConfig()->get('data-provider'))){
                        case 'sqlite':
                        case 'sqlite3':
                                $this->dataProvider = new SQLite3Provider(new \SQLite3($this->getDataFolder() . 'auctionHouse.sqlite'));
                                $this->getLogger()->notice('Set data provider to: SQLite3');
                                break;
                        case 'mysql':
                                $this->getLogger()->notice('MySQL is not supported yet.');
                                break;
                        case 'yml':
                        case 'yaml':
                                $this->getLogger()->notice('YAML is not yet supported.');
                                break;
                        case 'json':
                        case 'jason':
                                $this->getLogger()->notice('JSON is not yet supported.');
                                break;
                        default:
                                $this->getLogger()->notice('Unknown data provider. Using SQLite3...');
                                $this->dataProvider = new SQLite3Provider(new \SQLite3($this->getDataFolder() . 'auctionHouse.sqlite'));

                }
        }

        public function setFormIds(){
                $this->MAIN_UI_ID = mt_rand(111111, 999999);
                $this->CHOOSE_ITEM_UI_ID = $this->MAIN_UI_ID + 10;
                $this->SELL_UI_ID = $this->CHOOSE_ITEM_UI_ID + 10;
                $this->PLAYER_SHOP_UI_ID = $this->SELL_UI_ID + 10;
                $this->GLOBAL_SHOP_UI_ID = $this->PLAYER_SHOP_UI_ID + 10;
                $this->CONFIRM_PURCHASE_UI_ID = $this->GLOBAL_SHOP_UI_ID + 10;
                $this->MY_OFFERS_UI_ID = $this->CONFIRM_PURCHASE_UI_ID + 10;
                $this->CONFIRM_DELETE_OFFER_UI_ID = $this->MY_OFFERS_UI_ID + 10;
        }

        public function registerFormHandlers(){
                $handlers = [
                    [$this->MAIN_UI_ID, MainForm::class],
                    [$this->CHOOSE_ITEM_UI_ID, ChooseItemForm::class],
                    [$this->CONFIRM_DELETE_OFFER_UI_ID, ConfirmDeleteForm::class],
                    [$this->CONFIRM_PURCHASE_UI_ID, ConfirmPurchaseForm::class],
                    [$this->GLOBAL_SHOP_UI_ID, GlobalShopForm::class],
                    [$this->MY_OFFERS_UI_ID, MyOffersForm::class],
                    [$this->PLAYER_SHOP_UI_ID, MyOffersForm::class],
                    [$this->SELL_UI_ID, SellForm::class]
                ];
                foreach($handlers as $handler){
                        $this->formManager->registerHandler($handler[0], $handler[1]);
                }
        }

        /**
         * @return EconomyProvider
         */
        public function getEconomyProvider(): EconomyProvider{
                return $this->economyProvider;
        }

        /**
         * @param EconomyProvider $provider
         */
	public function setEconomyProvider(EconomyProvider $provider){
	        $this->economyProvider = $provider;
        }

        /**
         * @return DataProvider
         */
        public function getDataProvider(): DataProvider{
                return $this->dataProvider;
        }

        /**
         * @param DataProvider $provider
         */
        public function setDataProvider(DataProvider $provider){
                $this->dataProvider = $provider;
        }

        /**
         * @param string $message
         *
         * @return string
         */
        public function getMessage(string $message){
	      return $this->messages[$message];
        }

        /**
         * Saves cached data for later use
         */
        public function saveCache() {
	        file_put_contents($this->getDataFolder() . "cache.data", gzencode(json_encode($this->cache)));
        }


        /**
         * Loads cached data for later use
         */
        public function loadCache() {
	        $this->cache = json_decode(gzdecode(file_get_contents($this->getDataFolder() . "cache.data")), true);
        }

        /**
         * @param string $player
         */
        public function resetCache(string $player){
                if(isset($this->queue[$player])){
                        unset($this->queue[$player]);
                }
                if(isset($this->cache[$player])){
                        $this->cache[$player] = [];
                        $this->saveCache();
                }
        }

        /**
         * @param string $player
         *
         * @return array
         */
        public function getCache(string $player): array{
                if(isset($this->cache[$player])){
                        return $this->cache[$player];
                }else{
                        $this->resetCache($player);
                        return [];
                }
        }

        /**
         * @param Player $player
         */
        public function savePlayerItemNames(Player $player){
                $username = $player->getName();
                $items = $player->getInventory()->getContents();
                $this->cache[$username]["items"] = [];

                foreach($items as $key => $item){
                        $this->cache[$username]["items"][] = $key;
                }
        }

        /**
         * @param Player $player
         * @param Item   $item
         *
         * @return int
         */
        public function getCountFromItem(Player $player, Item $item): int{
                $count = 0;
                foreach($player->getInventory()->all($item) as $slot => $i){
                        $count += $i->getCount();
                }
                return $count;
        }

        /**
         * @param EnchantmentInstance[] $enchants
         *
         * @return string
         */
        public function formatEnchantmentIdsAsName(array $enchants): string{
                if(count($enchants) === 0){
                        return 'none';
                }
                $return = [];
                foreach($enchants as $enchant){
                        $return[] = $enchant->getType()->getName() . " (" . $enchant->getLevel() . ")";
                }
                return implode(", ", $return);
        }

        /**
         * @param Player $player
         *
         * @return array
         */
        public function getPaginationArray(Player $player): array {
                $username = $player->getName();
                $row_count = $this->getRowCount();
                $current_page = $this->queue[$username]['page'];
                for($i = 0, $total_pages = 0; $i < $row_count; $i += 10, $total_pages += 1){}
                return [$row_count, $current_page, $total_pages]; //amount of items, current page, amount of total pages
        }

        /**
         * @return int
         */
        public function getRowCount(): int{
                return $this->dataProvider->getRowCount();
        }

        /**
         * @param string $from
         *
         * @return array
         */
        public function fetchFromUsername(string $from): array {
                return $this->dataProvider->fetchFromUsername($from);
        }

        /**
         * @param string $id
         *
         * @return array
         */
        public function fetchFromId(string $id): array {
                return $this->dataProvider->fetchFromId($id);
        }

        /**
         * @return array
         */
        public function fetchAll(): array {
                return $this->dataProvider->fetchAll();
        }

        /**
         * @param int $marketId
         * @param int $skip
         *
         * @return array
         */
        public function fetchLastFromMarketID(int $marketId, int $skip): array {
                return $this->dataProvider->fetchLastFromMarketID($marketId, $skip);
        }

        /**
         * @param int $marketId
         *
         * @return array
         */
        public function fetchNextFromMarketID(int $marketId): array {
                return $this->dataProvider->fetchNextFromMarketID($marketId);
        }

        /**
         * @param string $id
         */
        public function deleteFromId(string $id) {
                $this->dataProvider->deleteFromId($id);
        }

        /**
         * @param Item   $item
         * @param Player $player
         * @param int    $price
         */
        public function insert(Item $item, Player $player, int $price) {
                $this->dataProvider->insert($item, $player, $price);
        }

        /**
         * Fetches what market items player will look at next
         *
         * @param Player $player
         *
         * @return null|array
         */
        public function fetchItems(Player $player){ // FILTHY FUNCTION
                $username = $player->getName();
                if(isset($this->queue[$username])){
                        $s = $this->queue[$username];
                        $type = $this->queue[$username]['type'];
                        switch($type){
                                case 0: // LAST PAGE
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
                                case 1: // NEXT PAGE
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
}