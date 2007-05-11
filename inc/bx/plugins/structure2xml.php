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


class bx_plugins_structure2xml extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    protected static $tableNames = array();
    protected static $childrenSections = array();
    protected static $tableInfo = array();
    private static $resources = array();
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_structure2xml($mode);
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
        bx_global::registerStream('structure2xml');
        $dom = new domDocument();
        $file = $this->getParameter($path,'src');
        
        $params = $this->getParameterAll($path);
        foreach($params as $key => $value) {
            $params[$key] = $this->translateScheme($value);
        }
        
        $query = bx_helpers_string::array2query($params);
        
        if(!empty($file)) {
            $uri = "structure2xml://$file/?$query";
            $dom->load($uri);
        }
        return $dom;
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    function stripRoot() {
        return true;
    }
    
    
    public function getChildren($coll, $id) {
        $children = array();
        $file = $this->getParameter($coll->uri,'src');
        foreach($this->getChildrenSections($file) as $section => $tvalue) {
            $params = $this->getParameterAll($coll->uri);
            $newp = array();
            
            foreach($params as $key => $value) {
                if (strpos($key,"structure2xml") === 0) {
                    $key = substr($key,14,-1);
                    $newp[$key] = $this->translateScheme($value);
                }
            }
            
            $st = new popoon_classes_structure2xml($this);
            $queries = $st->getQueries($file,array());
            $query = $queries[$tvalue['section']]['query'];
            $query = popoon_classes_structure2xml::replaceVarsInWhereStatic($query,$newp);
            
            /*if ($info['langField']) {
                $lang =   ',\'.\','.$table.'.'.$info['langField'];
            } else {
                $lang = "";
            }*/
            
            $where = substr($query,strpos($query,"from "));
            if ($pos = strpos($where,"order by")) {
                
                $where = substr_replace($where," group by uri ",$pos,0);
            } else {
                $where .= " group by uri ";
            }
            
            $query = "select " . popoon_classes_structure2xml::replaceVarsInWhereStatic($tvalue['inChildren'],$newp)."  $where ";
            
            $dbres =$GLOBALS['POOL']->db->query($query);
            if(MDB2::isError($dbres)) {
                throw new PopoonDBException($dbres);
            }
            $i = 1;
            while ($row = $dbres->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $c = $this->getResourceById($coll->uri,$row["uri"].".dbform");
                $c->displayName = $row['title'];
                $c->displayOrder = $i;
                $i++;
                $children[] = $c;
            }
            
        }
        
        return $children;
    }
    /* FIXME...
     * this is currently wrong
     */
    protected function getTableInfo($table) {
       return null;
	    if (!isset(self::$tableInfo[$table])) {
            $configfile = "forms/$table/config.xml";
            $confDom = new DomDocument();
            if (!@$confDom->load($configfile)) {
                if (file_exists($configfile)) {
                    //do it again for displaying the errors
                    $confDom->load($configfile);
                }
                return null;
            }
            //$where = $tvalue["where"];
            $confXp = new DomXpath($confDom);
            $confRes = $confXp->query("/bxco:config/bxco:chooser");
            if (!$confRes) {
                return null;
            }
            $fieldsNode = $confRes->item(0);
            self::$tableInfo[$table] = array();
            
            self::$tableInfo[$table]['chooser'] = str_replace("Chooser.","$table.",$fieldsNode->getAttribute("field"));
            $confRes = $confXp->query("/bxco:config/bxco:fields");
            $fieldsNode = $confRes->item(0);
            if ($webdavId = $fieldsNode->getAttribute("webdavid")) {
                self::$tableInfo[$table]['webdavId'] = "$table.$webdavId";
            } else {
                
                self::$tableInfo[$table]['webdavId'] = self::$tableInfo[$table]['chooser'] ;
            }
            
            self::$tableInfo[$table]['langField'] = $fieldsNode->getAttribute("langfield");
            
        }
	        
        return self::$tableInfo[$table];
    }
    
    protected function getChildrenSections($file) {
         if (!isset(self::$childrenSections[$file])) {
            self::$childrenSections[$file] = array();
            $dom = new domDocument();
            if (@$dom->load($file)) {
                $xp = new DomXPATH($dom);
                $res = $xp->query("/bxst:structure/bxst:section[@inChildren]");
                foreach($res as $node) {
                    $name = $node->getAttribute("name");
                    $this->childrenSections[$file][$name] = array();
                    $this->childrenSections[$file][$name]['where'] = $node->getAttribute("where");
                    $this->childrenSections[$file][$name]['section'] = $node->getAttribute("name");
                    $this->childrenSections[$file][$name]['inChildren'] = $node->getAttribute("inChildren");
                }
            } 
        }
        return self::$childrenSections[$file] ;  
        
    }
    
    protected function getTableNames($file) {
        
        if (!isset($this->tableNames[$file])) {
            $this->tableNames[$file] = array();
            $dom = new domDocument();
            $dom->load($file);
            $xp = new DomXPATH($dom);
            $res = $xp->query("/bxst:structure/bxst:section/bxst:table");
            foreach($res as $node) {
                $name = $node->getAttribute("name");
                $this->tableNames[$file][$name] = array();
                $this->tableNames[$file][$name]['where'] = $node->parentNode->getAttribute("where");
                $this->tableNames[$file][$name]['section'] = $node->parentNode->getAttribute("name");
            }
        }
        return $this->tableNames[$file] ;
        
    }
    /**
    * FIXME:
    * see getChildren for the new way...
    */
    public function getResourceById($path, $id, $mock = false) {
       if (! isset (self::$resources[$id])) {
            $file = $this->getParameter($path,'src');
            $res = new bx_resources_application_dbform($id);
            /*$tables = array_keys($this->getTableNames($file));	
            if(count($tables) > 0) {
	            $res->table = $tables[0];
        	    $info = $this->getTableInfo($tables[0]);
	            $res->webdavId = $info['webdavId'];
        	    $res->langField = $info['langField'];
	            $res->chooser = $info['chooser'];
	    }*/
            self::$resources[$id] = $res;
	
        }
        return self::$resources[$id] ;
    }

        
    /* needed for structure2xml code */    
    public function getAttrib($value) {
        return null;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        
        if ($ext == "dbform") {
            $res = $this->getResourceById($path, $id.".".$ext,$sample); 
            
            if ($res) {
                return $this;
            }
        } else {
            
            return null;
        }
    }
    
    
}
?>
