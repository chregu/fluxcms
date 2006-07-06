<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_fields_listview extends bx_dbforms2_field {

    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName = 'listview';
    }

    public function getConfigAttributes() {
        $ret = parent::getConfigAttributes();
        $ret['idfield'] = 'string';
        $ret['namefield'] = 'string';
        $ret['orderby'] = 'string';
        return $ret;
    }
    
    public function getSQLName() {
        return NULL;  
    }
   
    /**
     *  Returns the query which is needed to get the values for this list view.
     *
     *  @access public
     *  @return string SQL Query
     */
    public function getSelectQuery($options) {
        $table = $this->parentForm->tablePrefix.$this->parentForm->tableName;
        $query = ' SELECT '.$table.'.'.$this->attributes['idfield'].' AS _id, '.$this->attributes['namefield'].' AS _title';
        $query.= ' FROM '.$table;
        if($this->attributes['orderby'] != '') {
            $query.= ' ORDER BY '.$this->attributes['orderby'];
        }
        return $query;
    }
   
}

?>