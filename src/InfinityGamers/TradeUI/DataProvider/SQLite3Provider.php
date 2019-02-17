<?php
namespace InfinityGamers\TradeUI\DataProvider;
use pocketmine\item\Item;
use pocketmine\Player;
class SQLite3Provider implements DataProvider{
        /** @var \SQLite3 */
        protected $db;

        /**
         * SQLite3Provider constructor.
         *
         * @param \SQLite3 $db
         */
        public function __construct(\SQLite3 $db){
                $this->db = $db;

                $this->db->exec("CREATE TABLE IF NOT EXISTS history (id INTEGER PRIMARY KEY AUTOINCREMENT, item VARCHAR NOT NULL, username VARCHAR NOT NULL, price INTEGER NOT NULL)");
                $this->db->exec("UPDATE SQLITE_SEQUENCE SET seq = 1 WHERE name = 'history'");
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
                $q = $this->db->query("SELECT * FROM history WHERE LOWER(username) = LOWER('$from')");
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
         * @param Item   $item
         * @param Player $player
         * @param int    $price
         *
         */
        public function insert(Item $item, Player $player, int $price) {
                $q = $this->db->prepare("INSERT INTO history (item, username, price) VALUES (?, ?, ?)");
                $serialize = json_encode($item->jsonSerialize());
                $player = $player->getName();
                $q->bindParam(1, $serialize);
                $q->bindParam(2, $player);
                $q->bindParam(3, $price);
                $q->execute();
        }
}