<?php

class bx_cache_dummy {
    static protected $instance = NULL;
    
    private function __construct() {
        
    }
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bx_cache_dummy;
        }
        return self::$instance;
    }
    
    public function add($key, $val, $expires = null, $group = null) {
        return true;
    }
    
    public function replace($key, $val, $expires = null, $group = null) {
        return true;
    }
    
    public function set($key, $val,  $expires = null, $group = null) {
        return true;
    }
    
    public function del($key) {
        return true;
    }
    
    public function flush() {
        return true;
    }
    
    public function get($key) {
        return false;
    }
    
    public function getStats() {
        return array();
    }
    
}

?>
