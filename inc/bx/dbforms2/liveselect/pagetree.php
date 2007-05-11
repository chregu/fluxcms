<?php

class bx_dbforms2_liveselect_pagetree extends bx_dbforms2_liveselect {
    
    
    var $subtree = "/";
    
     public function getSelectQuery() {
         
          
        $limit = $this->limit;
        if(isset($this->currentPage)) {
            $limit = $this->currentPage * $this->limit.','.$this->limit;
        }
        
        $query = 'SELECT path AS _id, path as _title '. $this->getMainSelectQuery().' order by path  LIMIT '.$limit ;
        return $query;
     }
     
    protected function getMainSelectQuery() {
         $table = $this->tablePrefix."properties";
        $q = bx_helpers_string::utf2entities(utf8_encode($this->getNormalizedQuery()));
        
        
        if ($q) {
            $where.= " path like '%$q%' ";
        }
        if ($this->where) {
            $where .=" AND ". $this->replaceTablePrefix($this->where);
        }
        return 'from '.$table .' where '. $where .' AND path like "'.$this->subtree.'%" and name="output-mimetype" and value="httpd/unix-directory"';
        /*$q = bx_helpers_string::utf2entities(utf8_encode($this->getNormalizedQuery()));
        
        $table = $this->tablePrefix.$this->tableName;
        
        $whereFields = explode(',', $this->whereFields);
        $where = '(0 ';
        foreach($whereFields as $field) {
            $where.= "OR $field like '%$q%' ";
        }
        $where .=" ) ";
        
        $notNullFields = explode(',', $this->notNullFields);
        foreach($notNullFields as $field) {
			if($field != ''){
				$where.= "AND $field != 'NULL' ";
			}
        }
        
        if ($this->where) {
            $where .=" AND ". $this->replaceTablePrefix($this->where);
        }
        
        $orderby = !empty($this->orderBy) ? $this->replaceTablePrefix($this->orderBy) : $this->idField;
		$matcher = (!empty($this->getMatcher) AND isset($_GET[$this->getMatcher]) )? ' AND '.$this->getMatcher.' = "'.$_GET[$this->getMatcher].'" ' : '';
        return 'FROM '.$table.' '. $this->leftJoin .' WHERE '.$where.$matcher.' ORDER BY '.$orderby;*/
        
    }
}