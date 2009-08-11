<?php
/* not really useful right now , but it basically works */
class bx_dbforms2_liveselect_uid extends bx_dbforms2_liveselect {


    var $subtree = "/";

    public function getSelectQuery() { 

        $sql = " SELECT t2.value AS _id, t1.path as _title ". $this->getMainSelectQuery()."
        ORDER BY t1.path ";
        
        return $sql;
    }
     
    protected function getMainSelectQuery() {

        $table = $this->tablePrefix."properties";
        $q = bx_helpers_string::utf2entities(utf8_encode($this->getNormalizedQuery()));

        $where = " 1 AND ";

        if ($q) {
            $where.= " t1.path like '%$q%' AND ";
        }
        if ($this->where) {
            $where .= $this->replaceTablePrefix($this->where). " AND ";
        }

        $sql = " FROM $table AS t1, $table AS t2 WHERE
        $where
        t1.name='output-mimetype'
        AND t1.value='httpd/unix-directory' 
        AND t2.name='unique-id'
        AND t1.path = t2.path ";

        return $sql;
    }

}