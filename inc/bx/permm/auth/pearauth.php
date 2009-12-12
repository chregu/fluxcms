<?php

require_once 'Auth.php';

Class bx_permm_auth_pearauth extends bx_permm_auth_common {



    public function __construct($options = array()) {
        parent::__construct();
        $options = $this->initOptions($options);
        $this->MDB2Constructor($options, 'MDB2', array("advancedsecurity"));

    }
    // checks, if a password matches to the logged in user
    public function checkPassword($pass) {
        $username = $this->getUsername();
        return $this->fetchData($username, $pass);
    }



}


?>
