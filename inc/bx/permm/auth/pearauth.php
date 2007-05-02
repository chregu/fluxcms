<?php

require_once 'Auth/Auth.php';

Class bx_permm_auth_pearauth extends bx_permm_auth_common {
    
    
    
    public function __construct($options = array()) {
        parent::__construct($options);
        $this->MDB2Constructor($options,'MDB2');
        
    }
    // checks, if a password matches to the logged in user    
    public function checkPassword($pass) {
        $username =$this->getUsername();
        return $this->authObj->storage->fetchData($username, $pass);
    }
    


}


?>
