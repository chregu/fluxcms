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
class bx_dbforms2_fields_listview_12n extends bx_dbforms2_fields_listview {

    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName = 'listview';
        $this->type = 'listview_12n';
    }

    /**
     *  Returns an array containing all attributes which the field can handle.
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        $ret = parent::getConfigAttributes();
        $ret['thatidfield'] = 'string';
        $ret['orderby'] = 'string';
        return $ret;
    }

    /**
     *  Returns the query which is needed to get the values for this list view.
     *
     *  @param string $thatid The value of the related table id field.
     *  @access public
     *  @return string SQL Query
     */
    public function getSelectQuery($options) {
        $thatid = $options['thatid'];
        $table = $this->parentForm->tablePrefix.$this->parentForm->tableName;
        $query = ' SELECT '.$table.'.'.$this->attributes['idfield'].' AS _id, '.$this->replaceTablePrefix($this->attributes['namefield']).' AS _title';
        $query.= ' FROM '.$table;
        if(isset($this->attributes['leftjoin']) && $this->attributes['leftjoin'] !== '') {
            $query.= ' LEFT JOIN '.$this->replaceTablePrefix($this->attributes['leftjoin']);
        }
        $query.= ' WHERE '.$table.'.'.$this->attributes['thatidfield'].' = '.$GLOBALS['POOL']->db->quote($thatid);
        if(!empty($this->attributes['where'])) {
            $query.= ' AND '.$this->attributes['where'];
        }
        $query.= ' ORDER BY '.$this->replaceTablePrefix($this->attributes['orderby']);
        return $query;
    }
   
}
