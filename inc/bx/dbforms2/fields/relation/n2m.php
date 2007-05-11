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
class bx_dbforms2_fields_relation_n2m extends bx_dbforms2_field {

    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName = 'select';
        $this->type = 'relation_n2m';
        
    }

    /**
     *  Returns an array containing all attributes which the field can handle.
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['relationtable'] = 'string';
        $ret['thisidfield'] = 'string';
        $ret['thatidfield'] = 'string';
        $ret['linktothat'] = 'string';
        return $ret;
    }
    
    public function hasConfigValues() {
        return true;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getAdditionalData($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        
        $query = 'select '. $this->attributes['thatidfield'] . ' as _idfield from '. $tablePrefix.$this->attributes['relationtable'] .' where ' . $this->attributes['thisidfield'] .' = '.$db->quote($id);
        $res = $db->query($query);
        $v = array();
        if (!MDB2::isError($res)) {
            while ($row= $res->fetchRow(MDB2_FETCHMODE_ORDERED)) {
                $v[$row[0]] = $this->values[$row[0]];
            }
        }
        return $v;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLName() {
        return null;  
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function doAdditionalQuery($id) {
        if ($this->parentForm->queryMode == bx_dbforms2::QUERYMODE_DELETE) {
            return  $this->_deleteQuery($id);
        } else {
            return $this->_updateInsertQuery($id);
        }
        
        
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function _deleteQuery($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
         $query = "delete from ".$tablePrefix.$this->attributes['relationtable'] .' where '. $this->attributes['thisidfield'] . ' = '. $id ;
         $res = $db->query($query);
         return null;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function _updateInsertQuery($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
        $ids = array_keys($this->value);
        //delete not choosen ids
        if (count($ids) > 0) {
            $query = 'delete from '.$tablePrefix.$this->attributes['relationtable'] .' where ' . $this->attributes['thisidfield'] .' = '. $db->quote($id) . ' and not( ' . $this->attributes['thatidfield'] .' in (\''.implode("','",$ids).'\'))';
            $res = $db->query($query);
            //get old categories
            $query = 'select ' . $this->attributes['thatidfield'] .' from '.$tablePrefix.$this->attributes['relationtable'].' where ' . $this->attributes['thisidfield'] .' = '. $id .' and ( ' . $this->attributes['thatidfield'] .' in (\''.implode("','",$ids).'\'))';
            
            $res = $db->query($query);
            $oldids = $res->fetchCol();
        } else {
            $query = "delete from ".$tablePrefix.$this->attributes['relationtable'] .' where '. $this->attributes['thisidfield'] . ' = '. $db->quote($id) ;
            $res = $db->query($query);
            $oldids = array();
        }
        // add new categories
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $seqid = $db->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
                $query = 'insert into '.$tablePrefix.$this->attributes['relationtable']  .'(id, '. $this->attributes['thisidfield'] . ' , '. $this->attributes['thatidfield'] . " ) VALUES ($seqid,".$db->quote( $id).", ".$db->quote($value).")";
                $res = $GLOBALS['POOL']->db->query($query);
            }
        }
        
        return null;     
    }
    
    protected function getXMLAttributes() {
        
        return array('linktothat' => $this->attributes['linktothat']);
    }
   
}

?>