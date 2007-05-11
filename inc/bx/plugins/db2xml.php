<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
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
 * class bx_plugins_db2xml
 * @package bx_plugins
 * @todo write, what this plugin is used for
 * */
class bx_plugins_db2xml extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
        /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_db2xml($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    
    }

    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "$name";
    } 
    
    public function getContentById($path, $id) {
        bx_global::registerStream('db2xml');
        $dom = new domDocument();
 
        $table = $this->getParameter($path,'table');
        
        if(!empty($table)) {
            $where = '';
            $primary = $this->getParameter($path,'primary');
            if(!empty($primary)) {
                $where = "$primary=$id";
            } else {
                $where = $this->getParameter($path, 'where');
            }
            $uri = "db2xml://$table/";
            if(!empty($where)) {
                $uri .= "?where=$where";
            }
            $dom->load($uri);
        }

        return $dom;
        
    }

    public function isRealResource($path , $id) {
        return true;
    }
    
    public function stripRoot() {
        return true;
    }

    
}
?>
