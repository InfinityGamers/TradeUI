<?php
namespace InfinityGamers\TradeUI\Utils;
use pocketmine\level\Position;
use pocketmine\Player;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use function strlen;
use function stripos;
use function str_replace;
use function number_format;
use function is_numeric, is_int, is_float;
use function intval;
use function array_rand;
use function range;
use function preg_match_all;
use function preg_replace;
use function array_keys, array_values;
use function floatval;
use function floor;
class exc{
        const CHARS = [
            "=", "&", "<", ">", "/", "$", "#", "!", "-", "_", "+", ".", "@",
            "(", ")", "*", "^", "%", ";", ":", "?", "[", "]", "{", "}", "~"
        ];

        private static $lastColor = 0;

        /**
         *
         * @param            $string
         * @param array|null $elements
         *
         * @return mixed
         *
         */
        public static function _($string, array $elements = null){
                for($i = 0; $i < strlen($string) + 1; ++$i){
                        if(stripos("%$i", $string) !== false and isset($elements[$i - 1])){
                                $string = str_replace("%$i", $elements[$i - 1], $string);
                        }
                }
                $string = str_replace("%%", "\xc2\xa7", $string);
                return $string;
        }

        /**
         *
         * @param        $val
         * @param string $exploder
         *
         * @return string
         *
         */
        public static function double($val, $exploder = '.'){
                return number_format((float)$val, 2, $exploder, '');
        }

        /**
         *
         * @param $v
         *
         * @return bool|int
         *
         */
        public static function checkIsNumber($v){
                if(is_numeric($v)):return true;
                elseif(is_int($v)):return true;
                elseif(is_float($v)):return true;
                endif;
                return false;
        }

        /**
         *
         * @param $val
         *
         * @return int
         *
         */
        public static function stringToInteger($val){
                return self::checkIsNumber($val) ? intval($val) : 0;
        }

        /**
         *
         * @param array $val
         *
         * @return null
         *
         */
        public static function randomValue(array $val){
                if(empty($val)) return null;
                return $val[array_rand($val)];
        }

        /**
         *
         * @param $length
         *
         * @return int
         *
         */
        public static function randomNumber($length){
                $num = range(0, 9);
                $n = "";
                for($i = 0; $i < $length; ++$i){
                        $n .= exc::randomValue($num);
                }
                return intval($n);
        }

        /**
         *
         * @param            $length
         * @param bool|true  $numbers
         * @param bool|false $chars
         *
         * @return string
         *
         */
        public static function randomString($length, $numbers = true, $chars = false){
                $abc = range('A', 'Z');
                $num = range(0, 9);
                $str = "";
                if(!$numbers and !$chars){
                        for($i = 0; $i < $length; ++$i){
                                $str .= exc::randomValue($abc);
                        }
                }
                if($numbers){
                        for($i = 0; $i < $length / 2; ++$i){
                                $str .= exc::randomValue($abc);
                                $str .= exc::randomValue($num);
                        }
                }
                if($chars){
                        for($i = 0; $i < $length / 2; ++$i){
                                $str .= exc::randomValue($abc);
                                $str .= exc::randomValue(exc::CHARS);
                        }
                }
                return $str;
        }

        /**
         *
         * @param            $string
         * @param bool|false $numbers
         * @param bool|false $chars
         *
         * @return string
         *
         */
        public static function mixString($string, $numbers = false, $chars = false){
                $num = range(0, 9);
                $str = "";
                if(!$numbers and !$chars){
                        for($i = 0; $i < strlen($string); ++$i){
                                $str .= $string[$i];
                        }
                }
                if($numbers){
                        for($i = 0; $i < strlen($string); ++$i){
                                $str .= $string[$i];
                                $str .= exc::randomValue($num);
                        }
                }
                if($chars){
                        for($i = 0; $i < strlen($string); ++$i){
                                $str .= $string[$i];
                                $str .= exc::randomValue(exc::CHARS);
                        }
                }
                return $str;
        }

        /**
         *
         * @param $string
         *
         * @return array
         *
         */
        public static function getChars($string){
                preg_match_all("/[[:punct:]]/", $string, $m);
                return $m[0];
        }

        /**
         *
         * @param $string
         *
         * @return string
         *
         */
        public static function replaceChars($string){
                foreach(exc::getChars($string) as $char){
                        $string = str_replace($char, "", $string);
                }
                return $string;
        }

        /**
         *
         * @param $string
         *
         * @return string
         *
         */
        public static function replaceAllKeepLetters($string){
                return preg_replace("/[^A-Za-z]/", "", $string);
        }

        /**
         *
         * @param $text
         *
         * @return null|string|string[]
         *
         */
        public static function cleanString($text) {
                $utf8 = array(
                    '/[áàâãªä]/u'   =>   'a',
                    '/[ÁÀÂÃÄ]/u'    =>   'A',
                    '/[ÍÌÎÏ]/u'     =>   'I',
                    '/[íìîï]/u'     =>   'i',
                    '/[éèêë]/u'     =>   'e',
                    '/[ÉÈÊË]/u'     =>   'E',
                    '/[óòôõºö]/u'   =>   'o',
                    '/[ÓÒÔÕÖ]/u'    =>   'O',
                    '/[úùûü]/u'     =>   'u',
                    '/[ÚÙÛÜ]/u'     =>   'U',
                    '/ç/'           =>   'c',
                    '/Ç/'           =>   'C',
                    '/ñ/'           =>   'n',
                    '/Ñ/'           =>   'N',
                    '/–/'           =>   '-',
                    '/[’‘‹›‚]/u'    =>   ' ',
                    '/[“”«»„]/u'    =>   ' ',
                    '/ /'           =>   ' ',
                );
                return preg_replace(array_keys($utf8), array_values($utf8), $text);
        }

        /**
         *
         * @return mixed
         *
         */
        public static function randomColor(){
                $colors = ["&a", "&b", "&c", "&d", "&e"];

                self::$lastColor++;
                if(self::$lastColor > count($colors) - 1){
                        self::$lastColor = 0;
                }

                return $colors[self::$lastColor];
        }

        /**
         *
         * @param $string
         *
         * @return mixed
         *
         */
        public static function clearColors($string){
                $colors = ["&a", "&b", "&c", "&d", "&e", "&f", "&r", "&k", "&l", "&o"];
                for($i = 0; $i < 10; ++$i){
                        $string = str_replace("&$i", "", $string);
                }
                foreach($colors as $c){
                        $string = str_replace($c, "", $string);
                }
                return $string;
        }

        /**
         *
         * @param array $values
         *
         * @return array
         *
         */
        public static function returnArrayOfMultidimensionalArray(array $values){
                $result = [];
                $values = new RecursiveIteratorIterator(new RecursiveArrayIterator($values));
                foreach($values as $v){
                        $result[] = $v;
                }
                return $result;
        }

        /**
         *
         * @param Player $p
         * @param int    $farness
         *
         * @return Position
         *
         */
        public static function mirrorY(Player $p, $farness = 2){
                return new Position($p->x - $farness, $p->y, $p->z);
        }

        /**
         *
         * @param Player $p
         * @param int    $farness
         *
         * @return Position
         *
         */
        public static function mirrorX(Player $p, $farness = 2){
                return new Position($p->x - $farness, $p->y - $farness, $p->z);
        }

        /**
         *
         *
         * @param string $seconds
         *
         * @return int[]
         *
         */
        public static function secondsToDHMS(string $seconds){
                $hms = [];

                $hms[0] = floor($seconds / 86400);
                $hms[1] = floor($seconds / 3600);
                $hms[2] = floor(($seconds / 60) - (floor($seconds / 3600) * 60));
                $hms[3] = floatval($seconds % 60);

                return $hms;
        }
}