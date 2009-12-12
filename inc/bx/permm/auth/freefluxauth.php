<?php


Class bx_permm_auth_freefluxauth extends bx_permm_auth_pearauth {
    
    
    public function __construct($options = array()) {
        parent::__construct($options);
        
       
        $this->authObj->setLoginCallback(array($this,"loginCallback"));
        
        
    }
    
    /**
     * Wrapper function for the auth object - 
     * interface to the permm object,
     * to start authentication process
     *
     * @access  public
     * @return  void
     */
/*    public function start() {
         
        if (method_exists($this->authObj, "start")) {
            $this->authObj->start();
        }

        return NULL;
    }*/
    
    public function loginCallback($user) {
//        $prefix = substr($GLOBALS['POOL']->config->getTablePrefix(),0,-1);
	$host = substr(BX_WEBROOT,7,-1);
        $GLOBALS['POOL']->db->query("update freeflux_master.master set lastlogin = now(), logincount= logincount + 1 where alias = ".$GLOBALS['POOL']->db->quote($host) ." or host = ".$GLOBALS['POOL']->db->quote($host));
    }

}




?>
