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
class bx_dbforms2_fields_text_area extends bx_dbforms2_fields_text {
    
    public function __construct($name) {
        parent::__construct($name);

        $this->type = 'text_area';
        $this->XMLName = 'textarea';
        
        $this->attributes['rows'] = 12;
        $this->attributes['cols'] = 80;
    }
    
    protected function getXMLAttributes() {
        return array(
            'rows' => $this->attributes['rows'],
            'cols' => $this->attributes['cols'],
        );
    }

    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['rows'] = 'int';
        $ret['cols'] = 'int';
        return $ret;
    }
    
}
