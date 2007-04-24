<?php
class bx_helpers_openid {
    static protected $server = null;
    
    static function getServer() {
        if (!isset(self::$server)) {
            $store =  self::getOpenIDStore();
            //$serverURL = self::getServerURL();
            self::$server = new Auth_OpenID_Server( $store);
        }
        return self::$server;
    }
    
    static function getServerURL() {
        return BX_WEBROOT. "admin/webinc/openid/";
        
    }
    
    static function getOpenIDStore() {    
        //$a = new Auth_OpenID_FileStore("/tmp");
        return new Auth_OpenID_FileStore("/tmp");
    }
    
    
    static function setRequestInfo($info=null) {
        if (!isset($info)) {
            unset($_SESSION['openid_request']);
        } else {
            $_SESSION['openid_request'] = serialize($info);
        }
    }
    
    static function getRequestInfo() {
        return isset($_SESSION['openid_request'])
        ? unserialize($_SESSION['openid_request'])
        : false;
    }
    
    
}



/**
* Return whether the trust root is currently trusted
*/
function bx_openIdIsTrusted($identity_url, $trust_root) {
    if ($identity_url != BX_WEBROOT) {
        return false;
    }
    
    $query = "select uri from ".$GLOBALS['POOL']->config->getTablePrefix()."openid_uri where uri = ".$GLOBALS['POOL']->db->quote($trust_root)." limit 1";
    $res = $GLOBALS['POOL']->db->query($query);
    if ($res->numRows() > 0) {
        return true;
    }
    // from config.php
    
    return false;
    
}
