<?php

class bx_cache {
	protected $cache;
	static protected $instance = NULL;
    protected $prefix = '';
    
	private function __construct($driver) {
        $class = "bx_cache_$driver";
        $this->cache = call_user_func(array($class,'getInstance'));
        $this->prefix = BX_WEBROOT;
	}

	public static function getInstance($driver) {
		if (!isset(self::$instance)) {
			self::$instance = new bx_cache($driver);
		}
		return self::$instance;
	}

    
    public function add($key, $val, $expires = null, $group = null) {
        return $this->cache->add($this->prefix.$key, $val, $expires, $group);
    }
    
    public function replace($key, $val, $expires = null, $group = null) {
        return $this->cache->replace($this->prefix.$key, $val, $expires, $group);
    }
    
    public function set($key, $val,  $expires = null, $groups = null) {
        if ($groups) {
            if (!is_array($groups)) {
                $groups = array($this->prefix.$groups);
            } else {
                foreach ($groups as $k => $g) {
                    $groups[$k] = $this->prefix.$g;
                }
            }
            return $this->cache->set($this->prefix.$key, $val, $expires, $groups) ;
        } else {
            return $this->cache->set($this->prefix.$key, $val, $expires, null) ;
        }
    }
    
    public function del($key) {
        return $this->cache->del($this->prefix.$key);
    }
    
    public function flush($group = null) {
        if ($group) {
            return $this->cache->flush($this->prefix.$group);
        } else {
            return $this->cache->flush();
        }
        
    }
    
    public function get($key) {
        return $this->cache->get($this->prefix.$key);
    }
    
    public function getStats() {
        return $this->cache->getStats();
    }
    


}

?>
