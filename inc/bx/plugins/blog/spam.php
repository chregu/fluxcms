<?php


class bx_plugins_blog_spam {
    
    static protected $defaultIP = null; 
    static public $hostdoesnotexist = "does.not.exist.example.org";
    
    static function checkRBLs($urls) {
        $simplecache = popoon_helpers_simplecache::getInstance();
        $simplecache->cacheDir = BX_TEMP_DIR;
        
        $commentRejected = "";
        $spammerDat = $simplecache->simpleCacheRemoteImplodeRead('http://www.bitflux.org/download/antispam/refererspammer.dat','|',10800);
        
        $surblUrls = array();
        if ($spammerDat) {
            $spammerDatLines = explode("|",$spammerDat);
            
        }
        foreach ($urls as $check) {
            //bitflux.org spam check          
            // * Listed in Bitflux Spam Check      
            if ($spammerDat) {
                while ($_lines = array_splice($spammerDatLines,0,500)) {
                    if (preg_match("#".implode("|",$_lines). "#",$check)) {
                        $commentRejected .= "* bitflux blacklisted: $check\n";
                    }
                }
                
            }  
            /*preg_match($spammerDat,$check)) {
                $commentRejected .= "* bitflux blacklisted: $check\n";
            }*/
            if (preg_match("#(http://[\w\.\-^\"]+)#", $check,$matches)) {
                $surblUrls[] = $matches[0].'/';
            }
        }
        /* surbl check */
        if (!self::$defaultIP) {
            self::$defaultIP = gethostbyname(self::$hostdoesnotexist);
        }
        $surbl = new Net_DNSBL_SURBLWithDefaultDNS(self::$defaultIP);
        
        //check if surbl actually works...
        //if bitflux.ch is rejected, something is wrong and we don't check
        // against surbl anymore
        if ($surbl->isListed("http://bitflux.ch/")) {
            return $commentRejected;
        } else {
            $surblUrls = array_unique($surblUrls);
            foreach($surblUrls as $check2) {
                if ($surbl->isListed($check2)) {
                    $commentRejected .= "* surbl.org blacklisted: $check2\n";
                }        
            }
            
            return $commentRejected;
        }
    }
    
    static function checkSenderIPBLs($ip) {
       
        //don't check on localhost
        if ($ip == "127.0.0.1") {
            return "";
        }
        
        $blacklist = "xbl.spamhaus.org";
        
        //get defaultIP, if not already defined.
        if (!self::$defaultIP) {
            self::$defaultIP = gethostbyname(self::$hostdoesnotexist);
        }
        
        $d = new Net_DNSBL();
        $host = $d->getHostForLookup($ip, $blacklist);
        //get result
        $result = gethostbyname($host);
        
        //if result == defaultIP, something's wrong with the DNS, no blacklisting
        if (self::$defaultIP && self::$defaultIP == $result) {
            return "";
        }
        $commentRej = "";
        if ($result != $host) { 
            $commentRej .= "* xbl.spamhaus.org blacklisted sender IP ($ip <http://www.spamhaus.org/query/bl?ip=$ip>). \n";
        }
        return $commentRej;
    }
    
}

