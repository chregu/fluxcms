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
class bx_dbforms2_fields_color extends bx_dbforms2_field {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'color';
        $this->XMLName = 'input';
    }
    
    public function getSQLValue() {
        
        return preg_replace("/([0-9a-fA-F]{6,6})/","$1",$this->value);
    }
    
}

?>