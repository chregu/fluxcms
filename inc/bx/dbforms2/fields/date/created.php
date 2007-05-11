<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
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
class bx_dbforms2_fields_date_created extends bx_dbforms2_fields_date {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'date_created';
        $this->XMLName = 'input';
    }
    
    public function getSQLValue() {
        if($this->parentForm->queryMode == bx_dbforms2::QUERYMODE_INSERT) {
            return "NOW()";
        }
        
        return $this->value;
    }
    
    public function quoteSQLValue() {
        switch($this->parentForm->queryMode) {
            case bx_dbforms2::QUERYMODE_INSERT:
                return FALSE;
        }
        return TRUE;
    }
    
}

?>