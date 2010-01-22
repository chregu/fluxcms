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
// $Id: field.php 4831 2005-06-30 14:41:20Z philipp $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_fields_password_md5 extends bx_dbforms2_fields_password {
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function __construct($name) {
        parent::__construct($name);
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLName($type = null) {
        //bx_helpers_debug::webdump($this->parentForm->queryMode);
        $type = $this->parentForm->queryMode;
        if ($type  == bx_dbforms2::QUERYMODE_SELECT) {
            return "'' as " . $this->name;
        } else if ($type == bx_dbforms2::QUERYMODE_UPDATE && trim($this->value) == '' ) {
            return false;
        } else {
            return $this->name;
        }
    }

    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLValue() {
        if (strlen($this->value) != 32) {
            
            return md5($this->value);
        } else {
            return $this->value;
        }
    }
   
}

?>
