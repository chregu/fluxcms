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
class bx_dbforms2_sql {
    
    /**
     *  Returns a query to get the current record for the passed form.
     *
     *  @param  object $formr Form to generate the query for.
     *  @access public
     *  @return string Query to get the current record
     */
    public static function getSelectQueryByForm($form) {
        $db = $GLOBALS['POOL']->db;            
        
        $query = 'SELECT ';
        $fields = array();

        $fields[] = $form->idField;
        
        foreach($form->fields as $field) {
            if ($field->getAttribute('nosql')==false) {
                $name = $field->getSQLName('select');
                if ($name) {
                    //$fields[] = $db->quoteIdentifier($name);
                    $fields[] = $name;
                }
            }
        
        }
        
        $query.= implode(',', $fields);
        
        $query.= ' FROM '.$form->tablePrefix.$form->tableName;
        $query.= ' WHERE '.$form->idField.'='.$form->currentID;
        return $query;
    }
    
    /**
     *  Returns a query to get the current record for the passed form.
     *
     *  @param  object $formr Form to generate the query for.
     *  @access public
     *  @return string Query to get the current record
     */
    public static function getDeleteQueryByForm($form) {
        
        $query = 'DELETE ';
        $fields = array();

        
        $query.= ' FROM '.$form->tablePrefix.$form->tableName;
        $query.= ' WHERE '.$form->idField.'='.$form->currentID;
        
        return $query;
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function getUpdateQueryByForm($form) {
        $db = $GLOBALS['POOL']->db;            

        $table = $form->tablePrefix.$form->tableName;
        $query = "UPDATE $table SET";
        
        foreach($form->fields as $field) {
            if($field->name != $form->idField && $field->getAttribute('nosql')==false) {
                $col =  $field->getSQLName('update');
                if ($col) {
                    $value = $field->getSQLValue();
                    if($field->getAttribute('isxml') !== TRUE) {
                        $value = htmlspecialchars($value);
                    }
                    
                    $value = bx_helpers_string::utf2entities($value);
                    if($field->quoteSQLValue()) {
                        $value = $db->quote($value);
                    }
                    
                    $query.= ' '.$db->quoteIdentifier($col)."=$value,";
                }
            }
        }
        
        // cut off the last comma
        $query = substr($query, 0, -1);
        
        $idField = $form->idField;
        $currentID = $db->quote($form->currentID);
        $query.= " WHERE $idField = $currentID";            
        return $query;
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function getInsertQueryByForm($form) {
        $db = $GLOBALS['POOL']->db;            

        $fields = array();
        $values = array();
        
        // add the id field
        $fields[] = $form->idField;
        $values[] = $form->currentID;
        
        foreach($form->fields as $field) {
            if($field->name != $form->idField && $field->getAttribute('nosql')==false) {
                $name =  $field->getSQLName('insert');
                if ($name) {
                    $fields[] = $db->quoteIdentifier($name);
                    
                    $value = $field->getSQLValue();
                    if($field->getAttribute('isxml') !== TRUE) {
                        $value = htmlspecialchars($value);
                    }

                    $value = bx_helpers_string::utf2entities($value);
                    if($field->quoteSQLValue()) {
                        $value = $db->quote($value);
                    }
                    
                    $values[] = $value;
                }
            }
        }
        
        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $table = $form->tablePrefix.$form->tableName;

        $query = "INSERT INTO $table ($fields) VALUES ($values)";
        return $query;
    
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function getSelectQueryByLiveSelect($ls) {
        $db = $GLOBALS['POOL']->db;
        $q = bx_helpers_string::utf2entities($ls->getNormalizedQuery());
        
        $table = $ls->tablePrefix.$ls->tableName;
        
        $whereFields = explode(',', $ls->whereFields);
        $where = '(0 ';
        foreach($whereFields as $field) {
            $where.= "OR $field like '%$q%' ";
        }
        $where .=" ) ";

        $notNullFields = explode(',', $ls->notNullFields);
        foreach($notNullFields as $field) {
			if($field != ''){
				$where.= "AND $field != 'NULL' ";
			}
        }
        
        if ($ls->where) {
            $where .=" AND ". $ls->where;
        }
        $orderby = !empty($ls->orderBy) ? $ls->orderBy : $ls->idField;
		
		$matcher = ( !empty($ls->getMatcher) AND isset($_GET[$ls->getMatcher]) )? ' AND '.$ls->getMatcher.' = "'.$_GET[$ls->getMatcher].'" ' : '';
        
        $query = 'SELECT '.$table.'.'.$ls->idField.' AS _id, '.$ls->nameField.' AS _title FROM '.$table.' '. $ls->leftJoin .' WHERE '.$where.$matcher.' ORDER BY '.$orderby.' LIMIT '.$ls->limit;
        return $query;
    }
    
}

?>
