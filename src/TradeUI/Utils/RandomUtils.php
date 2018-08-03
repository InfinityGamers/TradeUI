<?php
namespace TradeUI\Utils;
use pocketmine\math\Vector3;
class RandomUtils{
        /** @var array */
        private static $t = [];

        /**
         *
         * @param            $string
         * @param array|null $elements
         *
         * @return mixed|string
         *
         */
        public static function textOptions($string, array $elements = null){
                $f = $string;
                if(isset(self::$t[$f])) $f = self::$t[$f];
                if(is_array($elements) && count($elements) >= 1){
                        $v = ["%%" => "%"];
                        $i = 0;
                        foreach($elements as $ret){
                                $v["%$i%"] = $ret;
                                ++$i;
                        }
                        $f = strtr($f, $v);
                }
                $f = str_replace("%n", "\n", $f);
                $f = str_replace("%%", "\xc2\xa7", $f);
                return $f;
        }

        /**
         *
         * @param $url
         *
         * @return mixed
         *
         */
        public static function getUrlContents($url){
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                $data = curl_exec($curl);
                curl_close($curl);
                return $data;
        }

        /**
         *
         * @param $str
         *
         * @return mixed
         *
         */
        public static function colorMessage($str){
                $str = str_replace("@rand_color", exc::randomColor(), $str);
                $str = str_replace("&", "\xc2\xa7", $str);
                return $str;
        }

        /**
         *
         * @return Vector3
         *
         */
        public static function randomVector(): Vector3{
                return new Vector3(mt_rand(0, 1000), mt_rand(0, 128), mt_rand(0, 1000));
        }

        /**
         *
         * @param $data
         *
         * @return array
         *
         */
        public static function objToArray($data){
                $array = [];
                if($data instanceof \stdClass or $data instanceof \ArrayObject){
                        foreach($data as $color => $hex){
                                $array[$color] = $hex;
                        }
                }
                return $array;
        }

}