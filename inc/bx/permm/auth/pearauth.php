<?php

require_once 'Auth/Auth.php';

Class bx_permm_auth_pearauth extends bx_permm_auth_common {
    
    
    
    public function __construct($options = array()) {
        parent::__construct($options);
        $opts = array(
            'dsn'           => $this->dsn,
            'usernamecol'   => $this->auth_usernamecol,
            'passwordcol'   => $this->auth_passwordcol,
            'gupicol'       => $this->auth_gupicol,
            'emailcol'      => $this->auth_emailcol,
            'idcol'         => $this->auth_idcol,
            'db_fields'     => $this->auth_dbfields,
            'cryptType'     => $this->auth_crypttype,
            );
        
        if (empty($options['auth_prependTablePrefix']) || $options['auth_prependTablePrefix'] == 'true') {
          $opts['table'] =  $GLOBALS['POOL']->config->getTablePrefix().$this->auth_table;
        } else {
            $opts['table'] = $this->auth_table;
        }
        
            
        // if someone tries to "login" via http_auth, let them do that :)
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $GLOBALS['POOL']->config->allowHTTPAuthentication == "true" ) {
            $opts['mode'] = '0644';
            $this->authObj = new Auth_HTTP("MDB2", $opts);
            $this->authObj->realm = 'Flux CMS HTTP Auth Login';
            
        } else {
            
            if (isset($GLOBALS['POOL']->config->sxip_homesite)) {
                $this->authObj = new Auth("sxip", $opts, "bxLoginFunction");
            } else {
                $this->authObj = new Auth("MDB2", $opts, "bxLoginFunction");
            }
        }
        
    }
    // checks, if a password matches to the logged in user    
    public function checkPassword($pass) {
        $username =$this->getUsername();
        return $this->authObj->storage->fetchData($username, $pass);
    }
    


}


/**
 * BX Function for Loginscreen
 * does nothing since loginscreen is 
 * handled via sitemap
 *
 */
function bxLoginFunction() {
    return true;
}

?>
