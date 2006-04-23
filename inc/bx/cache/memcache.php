<?php

class bx_cache_memcache {
    protected $cache;
    static protected $instance = NULL;
    
    private function __construct() {
        $this->cache = new Memcache();
        
        $servers = $GLOBALS['POOL']->config->cacheOptions;
        if ($servers) {
            foreach($servers as $server) {
                $this->connect($server['server'],$server['port'],$server['per'],$server['weight'],$server['timeout'],$server['retry']);
            }
        } else {
            if (!$this->connect('localhost')) {
                $this->cacheOn = false;
            }
        }
        //bx_helpers_debug::webdump($this->cache->getExtendedStats());
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bx_cache_memcache;
        }

        return self::$instance;
    }

    public function connect( $server, $port = 11211, $per = true, $weight = 1, $timeout = 1, $retry = 15 ) {
       
        return $this->cache->addServer($server, $port, $per, $weight, $timeout, $retry);
    }

    public function set($key, $val,  $expires = 604800, $groups = null) {
        if ($groups) {
            foreach($groups as $group) {
                $keys = $this->cache->get($group);
                if (!in_array($key,(array) $keys)) {
                    $keys[] = $key;
                    $this->cache->set($group,$keys);
                }
            }
        }
        return $this->cache->set($key, $val, false, $expires);
    }
     
    
 /*   public function add($key, $val, $expires = null, $group = null) {
        //error_log("add " . $key);
        return $this->cache->add($key, $val, false, $expires);
    }
      public function replace($key, $val, $expires = 25200, $group = null) {
        return $this->cache->replace($key, $val, false, $expire);
    }

    
    */

    public function del($key) {
        return $this->cache->delete($key);
    }

    public function flush($group = null) {
        if ($group) {
            $keys = $this->cache->get($group);
            foreach ((array) $keys as $key) {
                $this->cache->delete($key);
            }
        } else {
            return $this->cache->flush();
        }
    }

    public function get($keys) {
        //error_log("get " . $keys);
        return $this->cache->get($keys);
    }

  
  
   public function getStats() {

        return $this->cache->getExtendedStats();
    }

}

?>
