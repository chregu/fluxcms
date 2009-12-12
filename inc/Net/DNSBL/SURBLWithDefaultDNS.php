<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PEAR::Net_DNSBL_SURBLWithDefaultDNS                                  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Christian Stocker <chregu@php.net>                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@php.net>                          |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * PEAR::Net_DNSBL_SURBL
 *
 * This class acts as interface to the SURBL - Spam URI Realtime Blocklists.
 *
 * Services_SURBL looks up an supplied URI if it's listed in a
 * Spam URI Realtime Blocklists.
 */
include_once("Net/DNSBL/SURBL.php");

class Net_DNSBL_SURBLWithDefaultDNS extends Net_DNSBL_SURBL {
     
     var $hostdoesnotexist = "does.not.exist.example.org";
     var $defaultIP = null;
     
     function __construct() {
         $this->defaultIP = gethostbyname($this->hostdoesnotexist);
        
     }
     
     
     /** 
     * Checks if the supplied Host is listed in one or more of the
     * RBLs.
     *
     * @param  string Host to check for being listed.
     * @access public
     * @return boolean true if the checked host is listed in a blacklist.
     */
    function isListed($host)
    {
        
        $isListed = false;
        foreach ($this->blacklists as $blacklist) {
            $result = gethostbyname($this->getHostForLookup($host, $blacklist));
            if ($this->defaultIP && $this->defaultIP == $result) {
                continue;
            }
            if ($result != $this->getHostForLookup($host, $blacklist)) { 
                $isListed = true;
                //if the Host was listed we don't need to check other RBLs,
                break;
                
            } // if
        } // foreach
        
        return $isListed;
    } // function

    
    
}