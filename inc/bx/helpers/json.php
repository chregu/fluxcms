<?php
//load helper functions, if json_encode is not compiled in

if (!function_exists("json_encode")) {
    bx_helpers_json::$json = new JSON();
    
    function json_encode($value) {
        return bx_helpers_json::$json->encode($value);
    }
    
    function json_decode($value) {
        return bx_helpers_json::$json->decode($value);
    }
}


class bx_helpers_json {
    
    static public $json = null;
    
    
    static function encode($value) {
        
        return json_encode($value);
    }
    
    static function decode($value) {
        
        return json_decode($value);
    }
}
    
    