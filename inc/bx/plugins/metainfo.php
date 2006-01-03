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



class bx_plugins_metainfo extends bx_plugin {
    
    
    static private $instance = array();
    static private $idMapper = null;
    private $paramType = 'xml';

    public static function getInstance($mode) {
        if (!isset(bx_plugins_metainfo::$instance[$mode])) {
            bx_plugins_metainfo::$instance[$mode] = new bx_plugins_metainfo($mode);
        }
        
        return bx_plugins_metainfo::$instance[$mode];
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        if ($ext == "html") {
        return "$name.gallery.$ext";
        } else {
            
            return "$name.$ext";
        }
    }
    
    public function isRealResource($path, $id) {
        return TRUE;
    }
    
    
    public function getContentById($path, $id) {
        $metainfos = array();
        
        $ns = $this->getParameter($path, 'metaNs');
        $ns = ($ns == Null) ? 'bx:': $ns;
        
        $groupBy ='path';
        $params = $this->getParameterAll($path,$this->paramType);    
        $vroot = $this->getVirtualRoot($path);
        if ($vroot != Null) {
            
            $db = $GLOBALS['POOL']->db;
            $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
            if ($db) {
                $query = "SELECT path,name,value FROM ".$tablePrefix."properties as properties";
                $query.= "  WHERE ns IN('".implode("','", explode(",",$ns))."') AND path like '".$vroot."%'";
                $res = $db->query($query);
                
                if (!MDB2::isError($res)) {
                    $all = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
                    $this->groupResult($all, $metainfos, $groupBy);
                    $db2xml = new XML_db2xml($db, 'metainfos');
                    $db2xml->add($metainfos);
                    if (is_array($params)) { 
                        $db2xml->tagNameResult='params';
                        $db2xml->add($params);
                    }
                    return $db2xml->getXMLObject();
                    
                }
                
            }
                   
        }
        return Null;
    }

    protected function getVirtualRoot($uri) {
        if ($root = $this->getParameter($uri, 'virtualDir')) {
            return $root;
        }
        
        return Null;
    }
    
    
    protected function groupResult($results, &$metainfos, $groupBy) {
        foreach($results as $result) {
            if (!isset($metainfos[$result[$groupBy]])) {
                $metainfos[$result[$groupBy]] = array();
                $metainfos[$result[$groupBy]][$groupBy] = $result[$groupBy];
            }
            $metainfos[$result[$groupBy]][$result['name']] = $result['value'];
            
        }
    }
    
    
}



;



?>
