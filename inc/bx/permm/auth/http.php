<?php

require_once 'Auth/HTTP.php';

Class bx_permm_auth_http extends bx_permm_auth_common {
    
    
    
    public function __construct($options = array()) {
        parent::__construct($options);
       
        $opts = array(
            'dsn'           => $this->dsn,
            'mode'          => '0644',
            'table'         => $GLOBALS['POOL']->config->getTablePrefix().$this->auth_table,
            'usernamecol'   => $this->auth_usernamecol,
            'passwordcol'   => $this->auth_passwordcol,
            'cryptType'     => $this->auth_crypttype
        );
        $this->authObj = new Auth_HTTP("MDB2", $opts);
        
    }
    
    

}



?>
