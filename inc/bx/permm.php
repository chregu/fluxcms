<?php

Class bx_permm {

    static private $instance = null;
    
    private $authObj = null;
    private $permObj = null;
    private $userId = null;
    
    private function __construct($options) {
        $this->permm = $options;
        if (!isset($options['permModule']) || !$options['permModule']['type'] ) {
            $this->permObj = null;
        } else {
            $this->authObj = bx_permm::factory($options['authModule'], 'auth');
            $this->authObj->start();
             $this->permObj = bx_permm::factory($options['permModule'], 'perm');
             $this->userId = $this->authObj->getUserId(); 
        }
    }
    
    
    public static function getInstance($options = NULL) {
        if (!bx_permm::$instance instanceof bx_permm) {
            //$options can only be null, if we already have an instance
            if ($options == NULL) {
                  $conf = bx_config::getInstance();
                  $options =  $conf->getConfProperty('permm');
                //throw new Exception("You didn't provide any Permmission Options");
            }
            bx_permm::$instance = new bx_permm($options);
        }
        
        return bx_permm::$instance;
    }
    
    
    /**
    * Wrapper function for Auth module to start
    * authentication process
    * 
    * @return   void
    * @access   public
    */
    public function start() {
        if(method_exists($this->authObj, "start")) {
            
            
            $this->authObj->start();
            $this->userId = $this->authObj->getUserId(); 
        }
    }
    
    
    /**
    * Wrapper function for Auth module's getAuth() method
    *
    * @return   boolean     true|false
    * @access   public
    */
    public function getAuth() {
        if (!$this->authObj) {
            $this->authObj = bx_permm::factory($this->permm['authModule'], 'auth');
            if ($this->authObj) {
                $this->authObj->start();
            }
        }
        return $this->authObj->getAuth();
    }
    
    
    /**
    * Wrapper function for Auth module's logout() method
    *
    * @return   void
    * @access   public
    */
    public function logout() {
         if (!$this->authObj) {
            $this->getAuth();
        }
        
        if (!empty($_COOKIE['fluxcms_login'])) {
            setcookie('fluxcms_login', '', 0,"/",null,null,true);
            unset($_COOKIE['fluxcms_login']);
        }
        
        
            
        if (method_exists($this->authObj, "logout")) {
            $this->authObj->logout();
        }
    }
    
    public function getUsername() {
        if (!$this->authObj) {
            $this->getAuth();
        }
        return $this->authObj->getUsername();
    }
    
    public function getUserId() {
        return $this->authObj->getUserId();
    }
    
    public function getUserGid() {
        return $this->authObj->getUserGid();
    }
    
    public function isLoggedIn() {
        if ($this->getUsername()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Factory to instanciate appropriate auth and perm module
    * 
    * @param    array   $options    options contianing type,dsn,...
    * @param    string  $mod        module category (auth|perm)
    * @return   mixed   object|null 
    * @access   private
    *
    */
    private function factory($options,$mod) {
        if (isset($options['type']) && !empty($options['type'])) {
            $module = sprintf("bx_permm_%s_%s", $mod, $options['type']);
            if (class_exists($module) && !$this->authObj instanceof $module) {
                return new $module($options);
            }
        }
        
        return NULL;
    }
    
    public function isAllowed($uri,$actions) {
        if ($this->permObj) {
            return $this->permObj->isAllowed($uri,$actions,$this->userId);
        } else {
            if (in_array('admin',$actions) && !$this->userId) {
                // try to get the authObj
                $this->getAuth();
                // check for a userid
                $this->userId = $this->authObj->getUserId();
                if ($this->userId) {
                    return true;
                }
                return false;
            }
            return true;
        }
    }
    
    /*
     * Check if the permission systems allows to edit permissions online
     */
    public function isEditable() {
        if ($this->permObj) {
            return $this->permObj->isEditable();
        } else {
            return false;
        }    	
    }
    
    public function checkPassword($password) {
        return $this->authObj->checkPassword($password);
    }

    /**
     * movePermissions() is called, when a colleciton is moved
     * 
     * @param mixed $from_uri 
     * @param mixed $to_uri 
     * @return void
     */
    public function movePermissions($from_uri, $to_uri)
    {
        if ($this->permObj && method_exists($this->permObj, "movePermissions")) {
            $this->permObj->movePermissions($from_uri, $to_uri);
        }
        return NULL;
    }
    
}
?>
