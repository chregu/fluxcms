<?php

class bx_helpers_sql {
    
    static public $lastInsertId = null;
    
     static public function quotePostData($data) {
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        $quoted = array();
        foreach($data as $key => $value) {
            $quoted[$key] = $dbwrite->quote(bx_helpers_string::utf2entities($value));
        }
        return $quoted;
    }
    
    static public function getUpdateQuery($table, $data, $fields = array(), $id = NULL) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "UPDATE ".$tablePrefix.$table." SET ";
        foreach($data as $name => $value) {
            if(in_array($name, $fields) OR sizeof($fields) == 0) {
                $query.= "$name = $value,";
            }
        }
        // cut off the last comma
        $query = substr($query, 0, -1);
        
        if(isset($id)) {
            $query.= " WHERE id=$id";
        }
        return $query;
    }
    
    static public function getInsertQuery($table, $data, $fields = array(), $id = NULL) {
        
        $qfields = array();
        $qvalues = array();
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        
        
        foreach($data as $name => $value) {
            if((in_array($name, $fields) || (sizeof($fields) == 0)) && $value !== NULL ) {
                $qfields[] = $name;
                $qvalues[] = $value;
            }
        }
        
        if(!isset($id)) {
            $id = $dbwrite->nextID($tablePrefix."_sequences");
            self::$lastInsertId = $id;
        } else {
            self::$lastInsertId = $id;
        }
        
        $qfields[] = 'id';
        $qvalues[] = $id;
        
        $qfields = implode(',', $qfields);
        $qvalues = implode(',', $qvalues);
        
        return "INSERT INTO ".$tablePrefix.$table." ($qfields) VALUES ($qvalues)";
    }
    
    static public function getDeleteQuery($table, $id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        return "DELETE FROM ".$tablePrefix.$table." WHERE id=$id"; 
    }
    
    static public function updateCategoriesTree($blogid) {
        // this is the same code as in forms/blogcategories/updatetree.php
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        //clear cache for that table
        
        $GLOBALS['POOL']->cache->flush("table_blogcategories");
        
        $tree = new SQL_Tree($dbwrite);
        $tree->idField = "id";
        $tree->referenceField = "parentid";
        $tree->tablename = $tablePrefix."blogcategories";
        $tree->FullPath = "fulluri";
        $tree->FullTitlePath  = "fullname";
        $tree->Path = "uri";
        $tree->Title = "name";
        $tree->fullnameSeparator = " :: ";
        $data = array("name","uri","fulluri");
        
        $rootQuery = "select id from ".$tablePrefix."blogcategories where parentid = 0 and blog_id = ".$blogid;
        $rootid = $dbwrite->queryOne($rootQuery);
        if (!$rootid) {
            print '<font color="red">You don\'t have a root collection, please define one</font><br/>
                    Otherwise the category output will not be correct<br/><br/>';
        } else {
            $tree->importTree($rootid,true,"name","","",(($blogid-1)*1000)+1);
        }    
    }
    
    /**
     *  Queries all fields of the given table using the given id and returns 
     *  exactly one entry.
     *
     *  @param  string $table Tablename
     *  @param  string $id The id of the entry to get
     *  @param  string $idField If the id field is not called 'id', use this to override it
     *  @access public
     *  @return array The queried entry on success or FALSE on failure.
     */
    
    public static function getOneRowById($table, $id, $idField = 'id') {
        $tp = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;

        $query = "select * from {$tp}{$table} where {$idField} = $id";
        bx_log::log($query);
        $res = $db->query($query);
        if(!MDB2::isError($res) && $res->numRows() > 0) {
            return $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        }
        
        return FALSE;
        
    }
    
}
