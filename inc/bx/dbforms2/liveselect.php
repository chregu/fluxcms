<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
* DOCUMENT_ME
*
* @package bx_dbforms2
* @category 
* @author Liip AG      <contact@liip.ch>
*/
class bx_dbforms2_liveselect {
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $nameField = '';
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $whereFields = '';
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $limit = 10;
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $idField = 'id';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $tableName = '';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $tablePrefix = '';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $query = '';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $leftJoin = '';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
    public $orderBy = 'id';
    
    /**
    *  DOCUMENT_ME
    *  @var var
    */
	public $getMatcher = '';
	
    /**
    *  DOCUMENT_ME
    *  @var var
    */
	public $notNullFields = '';
    
    public $currentPage = null;
    
    /**
    *  Normalizes the liveselect query (e.g. strips whitespace)
    *
    *  @access public
    *  @return string
    */
    public function getNormalizedQuery() {
        // strip ws
        return trim($this->query);
    }
    
    /**
    *  Sets an optional left join
    *
    *  @param  string $leftJoib The left join incl. condition, SQL
    *  @access public
    */
    public function setLeftJoin($leftJoin) {
        if ($leftJoin) {
            $this->leftJoin = "left join ".$this->replaceTablePrefix($leftJoin);
        }
    }
    
    /**
    *  Replaces all occurences of {tablePrefix} in the given string with the
    *  current table prefix.
    *
    *  @param  string $strIn Input string
    *  @access protected
    *  @return string
    */
    protected function replaceTablePrefix($strIn) {
        return str_replace('{tablePrefix}', $this->tablePrefix, $strIn);
    }
    
    protected function getMainSelectQuery() {
        $q = bx_helpers_string::utf2entities(utf8_encode($this->getNormalizedQuery()));
        
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
        return 'FROM '.$table.' '. $this->leftJoin .' WHERE '.$where.$matcher.' ORDER BY '.$orderby;
        
    }
    
    public function getSelectQuery() {
        $table = $this->tablePrefix.$this->tableName;
        $limit = $this->limit;
        if(isset($this->currentPage)) {
            $limit = $this->currentPage * $this->limit.','.$this->limit;
        }
        
        $query = 'SELECT '.$table.'.'.$this->idField.' AS _id, '.$this->replaceTablePrefix($this->nameField).' AS _title '.$this->getMainSelectQuery().' LIMIT '.$limit;
        return $query;
    }
    
    protected function getNumPages() {
        $query = 'SELECT count(*) '.$this->getMainSelectQuery();
        $res = $GLOBALS['POOL']->db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        
        if($row) {
            return ceil($row[0] / $this->limit);
        }
        return 1;
    }
    
    public function appendPagerNode($xml) {
        $pagerNode = $xml->createElement('pager');
        $numPages = $xml->createElement('numpages', $this->getNumPages());
        $pagerNode->appendChild($numPages);
        $xml->documentElement->appendChild($pagerNode);
    }
    
}

?>
