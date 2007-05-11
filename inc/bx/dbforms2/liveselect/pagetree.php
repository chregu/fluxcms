<?php
/* not really useful right now , but it basically works */
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
            $where.= " path like '%$q%' AND ";
        }
        if ($this->where) {
            $where .= $this->replaceTablePrefix($this->where). " AND ";
        }
        return 'from '.$table .' where '. $where .'  path like "'.$this->subtree.'%" and name="output-mimetype" and value="httpd/unix-directory"';

    }
}