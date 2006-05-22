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
 * An uri field which garantees key(uri) uniqueness.
 *
 * @package bx_dbforms2
 * @category 
 * @author Philipp Stucki <philipp@bitflux.ch>
 */
class bx_dbforms2_fields_text_uri extends bx_dbforms2_fields_text {
    
    public function __construct($name) {
        parent::__construct($name);

        $this->type = 'text_uri';
        $this->XMLName = 'input';
    }

    public function getSQLValue() {
        
        switch($this->parentForm->queryMode) {
            // make an unique uri when inserting a new entry
            case bx_dbforms2::QUERYMODE_INSERT:
                return $this->getUniqueUri($this->value);
            break;
        }
        
        return $this->value;
    }
    
    /**
     *  Returns a unique uri by querying for the given uri and modifying it.
     *
     *  @param  string $uri The original base uri
     *  @param  string $uri The id of the current entry (in upate mode only)
     *  @access public
     *  @return string A new uri which is unique
     */
    
    public function getUniqueUri($uri) {
        //check if uri already exists
        if(trim($uri) == '') {
            $uri = 'none';
        }
        $query = "SELECT ".$this->parentForm->idField." FROM ".$this->parentForm->tablePrefix.$this->parentForm->tableName." WHERE ".$this->getSQLName()." = '$uri'";
  
        $resid = $GLOBALS['POOL']->db->query($query);
        $newuri = $uri;
        $z = 1;
        while ($resid->numRows() > 0) {
            $z++;
            $newuri = $uri . "-". $z;
            $query = "SELECT ".$this->parentForm->idField." FROM ".$this->parentForm->tablePrefix.$this->parentForm->tableName." WHERE ".$this->getSQLName()." = '$newuri'";
            $resid = $GLOBALS['POOL']->db->query($query);
        }
        $uri = $newuri;
 
        return $uri;
        
    }
    
    
}

?>
