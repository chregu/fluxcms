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


class bx_plugins_sql2xml extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_sql2xml($mode);
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

        
        if ($this->getParameter($path,"simple")  == "true") {
        	$db2xml = new XML_db2xml(NULL,'result');
        } else {
	        $db2xml = new XML_db2xml(NULL, NULL, 'Extended');
	}
        $xml = '';
        
        $options = array(
        'formatOptions' => array (
        'xml_seperator' => '',
        'element_id' => 'id'
        )
        );
        $db2xml->Format->SetOptions($options);
        
        if ($this->getParameter($path,"contentIsXml")  == "true") {
            $db2xml->setContentIsXml(true);
        }
        
        $queries = $this->getParameterAll($path,"sql");
        $queries['default'] = $this->getParameter($path, "sql");

	$db = $GLOBALS['POOL']->db;
	foreach ($queries as $key => $query) {
        	if(MDB2::isManip($query)) {
			// "<nothingFound>manipulative queries are not allowed</nothingFound>";
		} else {
        		$res = $db->query($query);
		        if (PEAR::isError($res) || $res->numRows() == 0) {
        		    //$xml = "<nothingFound/>";    
	        	} else {
      	    			$db2xml->addWithInput("Dbresult",$res);
        		}
		}
        }
	$xml = $db2xml->getXMLObject();
        return $xml;
        
        
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function stripRoot() {
        return true;
    }
    
    
}
?>
