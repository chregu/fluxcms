<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+


class bx_plugins_rss extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
        /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_rss($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    
    }
    
     public function getContentById($path, $id) {
        $dom = new domDocument();
        $sc = popoon_helpers_simplecache::getInstance();
        $src= $this->getParameter($path, "src");
        if ($src) {
        
        try { 
            $bla = $sc->simpleCacheHttpRead($src,3600);
        } catch ( Exception $e) {
            throw new Exception ("Couldn't load $src, 'cause of :<br/>". $e->getMessage());
        }
            
        if (!$bla) {
            throw new exception("Could not load $src");
        }
        
        $dom->loadXML($bla);
        
        return $dom;
        } else {
            return null;
        }
    }
    
    
}