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
// $Id: n2m.php 9167 2007-10-30 16:21:21Z chregu $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_fields_query_distinct extends bx_dbforms2_field {

    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName = 'select';
        $this->type = 'group_distinct';
    }

    
    public function hasConfigValues() {
        return true;
    }
    
    /**
     * FIX ME
     * 
     */
    public function getXMLAttributes() {
        $this->_getValues();
        return array();
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    private function _getValues() {
        
        print_r($this->attributes);
        
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;     
        $query = 'SELECT distinct('. $this->name. ') as _idfield FROM ';
        $query .=  $tablePrefix.$this->parentForm->tableName;
        if($this->attributes['where'] != '' ) {
            $query .= ' WHERE  '.$this->attributes['where'];
        }
        $res = $db->query($query);
        $v = array('' => 'Please select...');
        if (!MDB2::isError($res)) {
            while ($row= $res->fetchRow(MDB2_FETCHMODE_ORDERED)) {
                if($row[0] != '') {
                    $v[$row[0]] = $row[0];
                }
            }
        }
        $this->setValues($v);
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLName() {
        return $this->name;  
    }
    
    /**
     *  Returns an array containing all attributes which the field can handle.
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['where'] = 'string';
        return $ret;
    }

    
   
}

?>