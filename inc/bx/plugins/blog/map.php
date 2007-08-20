<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
        
class bx_plugins_blog_map extends bx_plugin {
    
    static public $instance = array();
    protected $res = array();
    
    protected $db = null;
    protected $tablePrefix = null;
    
    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_blog_map($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        
        return $name.'.'.$this->name;
        
    }
    
    
    public function getContentById() {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        
        $query = "select id, post_info from ".$tablePrefix."blogposts where post_info != ''";
        
        $res = $db->query($query);
        
        $xml = "";
        
        if(!MDB2::isError($res)) {
            $xml .= "<locations>";
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $row_replaced = preg_replace('# #', '', $row['post_info']);
                $row_splited = preg_split('#\n#', $row_replaced);
                //bx_helpers_debug::webdump($row_splited);
                $xml .= "<location>";
                $xml .= preg_replace("#plazename#", "name", $row_splited[3]);
                $xml .= preg_replace("#plazelon#", "name", $row_splited[2]);
                $xml .= $row_splited[1];
                $xml .= "</location>";
            }
            $xml .= "</locations>";
        }
        $dom = new DomDocument();
        $dom->loadXML($xml);
        return $dom;
    }
}
?>
