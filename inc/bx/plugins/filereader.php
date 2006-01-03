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


class bx_plugins_filereader extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_filereader($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    }

    public function getContentById($path, $id) {
        $src = $this->getParameter($path, 'src');
        if (!file_exists($src) ) {
            throw new PopoonFileNotFoundException($src);
        }
        $dom = new DOMDocument();
        if(!$dom->load($src)) {
            throw new PopoonXMLParseErrorException($src);
        }
                 
        return $dom;
    }
    
    public function isRealResource($path , $id) {
        return TRUE;
    }
    
}