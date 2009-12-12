<?php
class bx_helpers_int {
    
    public static function getRand($min=0, $max=10000) {
        
        $rand = mt_rand($min, $max);
        $rand = ($rand < $min) ? $min : $rand;
        $rand = ($rand > $max) ? $max : $rand;
        
        return $rand;
        
    }
    
    public static function getRandomHex($hardpredictableData = '') {
        
        return md5(uniqid( microtime() . mt_rand(),true) . $GLOBALS['POOL']->config->magicKey.$hardpredictableData);
    }
    

    public static function getMultiRandsXML($num=1, $min=0, $max=1000) {
        $rands = array();
        for($i=0;$i<$num;$i++) {
            $rand = mt_rand($min, $max);
            $rand = ($rand < $min) ? $min : $rand;
            $rand = ($rand > $max) ? $max : $rand;
            if (!in_array($rand, $rands)) {
                array_push($rands, $rand);
            } else {
                $i--;
            }
        }
        
        $rdom = bx_helpers_string::explodeToNode(',', implode(",", $rands), 'entry', 'rands');
        if ($rdom) {
            return $rdom;
        }
    }
    
    public static function getSequence($max) {
        @session_start();
        if (!isset($_SESSION['bx_helpers_sequence']) || $_SESSION['bx_helpers_sequence'] > $max) {
            $_SESSION['bx_helpers_sequence'] = 1;
        }
        return $_SESSION['bx_helpers_sequence']++;
         
    }
}

?>
