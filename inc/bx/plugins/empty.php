<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
/**
 * class bx_plugins_empty
 * @package bx_plugins
 */
class bx_plugins_empty extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
        /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_empty($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        return "$path.$name";
    }
        
    
     public function getContentById($path, $id) {
        $dom = new domDocument();
        return $dom;
      
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    
}
