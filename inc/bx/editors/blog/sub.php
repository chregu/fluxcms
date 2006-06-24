<?php

class bx_editors_blog_sub {
    protected $tablePrefix;
    public $lastInsertId = FALSE;
    static protected $instance = null;
    
    public function __construct() {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->dbwrite = $GLOBALS['POOL']->dbwrite;
    }
    
    
    public function handlePOST($path, $id, $data) {
    }
    
    protected function quotePostData($data) {
        $quoted = array();
        foreach($data as $key => $value) {
            $quoted[$key] = $this->dbwrite->quote(bx_helpers_string::utf2entities($value));
        }
        return $quoted;
    }
    
    protected function getUpdateQuery($table, $data, $fields = array(), $id = NULL) {
        $query = "UPDATE ".$this->tablePrefix.$table." SET ";
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
    
    protected function getInsertQuery($table, $data, $fields = array(), $id = NULL) {
        $qfields = array();
        $qvalues = array();
       
        foreach($data as $name => $value) {
            if(in_array($name, $fields) OR sizeof($fields) == 0) {
                $qfields[] = $name;
                $qvalues[] = $value;
            }
        }
        
        if(!isset($id)) {
            $id = $this->dbwrite->nextID($this->tablePrefix."_sequences");
            $this->lastInsertId = $id;
        } else {
            $this->lastInsertId = $id;
        }
        
        $qfields[] = 'id';
        $qvalues[] = $id;
        
        $qfields = implode(',', $qfields);
        $qvalues = implode(',', $qvalues);

        return "INSERT INTO ".$this->tablePrefix.$table." ($qfields) VALUES ($qvalues)";
    }
    
    protected function getDeleteQuery($table, $id) {
        return "DELETE FROM ".$this->tablePrefix.$table." WHERE id=$id"; 
    }
    
}


