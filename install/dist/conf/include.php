<?php


$GLOBALS['POOL']->config->setOutputCacheCallback("bx_cachecallback");


function bx_cachecallback() {
 
    //default, send a 304, if possible
    // change this to true, if you want to have outputcaching in general;
    // change it to false for no caching at all
    $default = 304;
    
    if ($default === false) {
        return false;
    }
    
    //do not cache, if in admin section
    if (strpos($_GET['path'],"admin/") !== false) {
        return false;
    }
    
    //do not cache, if not a GET request
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        return false;
    }

    // do not cache, if logged in
    if (isset($_COOKIE[session_name()])) {
        @session_start();
        
        if (isset($_SESSION['_authsession']) && isset($_SESSION['_authsession']['registered']) && $_SESSION['_authsession']['registered']) {
            return false;
            
        }
    }
    
    //load based caching, only works on linux :)
    /*
    if ($default !== true) { 
        $load = substr(file_get_contents("/proc/loadavg"),0,4);
        if ($load > 2) {
            return true;
        } 
    }
    */
    
    return $default;
}
