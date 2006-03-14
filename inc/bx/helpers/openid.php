<?php
class bx_helpers_openid {
    static protected $server = null;
    
    static function getServer() {
        if (!isset(self::$server)) {
            self::$server = new Auth_OpenID_Server(self::getServerURL(), self::getOpenIDStore());
        }
        return self::$server;
    }
    
    static function getServerURL() {
        $path = $_SERVER['SCRIPT_NAME'];
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $s = $_SERVER['HTTPS'] ? 's' : '';
        if (($s && $port == "443") || (!$s && $port == "80")) {
            $p = '';
        } else {
            $p = ':' . $port;
        }
        
        return "http$s://$host$p$path";
    }
    
    static function getOpenIDStore() {    
        return new Auth_OpenID_FileStore("/tmp");
    }

    
}

/**
 * Return whether the trust root is currently trusted
 */
function isTrusted($identity_url, $trust_root)
{
    // from config.php
    if ($identity_url != BX_WEBROOT) {
        return false;
    }

    $sites = array();// getSessionSites();
    //FIXME, get from DB authorized trust_roots
    return true;
    return isset($sites[$trust_root]) && $sites[$trust_root];
}
