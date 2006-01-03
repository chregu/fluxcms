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

/** A plugin to get metadata (especially dublincore stuff
* in a recursive manner. 
* 
* Best used as portlet, right now.. You'd most certainly 
* have to adjust getIdByRequest() if you want to use it as a normal
* plugin.
*
* The data is get directly from the properties table...
*/

class bx_plugins_metadata extends bx_plugin  {
    
    private static $instance = array();
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_metadata($mode);
        } 
        
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->mode = $mode;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        return "/$name.$ext";
    }   
    
    
    public function getContentById($path, $id) {
        $dom = new domDocument();
        
        $query = "select name,value, path  from properties where (".$this->getWherePart($id).") and ns = 'dc:' order by path DESC";
        
        $res = $GLOBALS['POOL']->db->query($query);
        $root = $dom->appendChild($dom->createElement("metadata"));
        while ($row = $res->fetchRow(MDB2_FETCHMODE_NUMBER)) {
            $n = $root->appendChild($dom->createElement($row[0],$row[1]));
            $n->setAttribute("path", $row[2]);
        }
        return $dom;
        
    }
    protected function getWherePart($dir) {
        
        $query = "path = '$dir' or path='/'";
        while ($pos = strrpos ($dir,"/")) {
            $dir = substr($dir, 0, $pos );
            $query = "path = '$dir/' or " .$query;
        }
        return $query;
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function stripRoot() {
        return true;
    }
}