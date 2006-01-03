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
class bx_dbforms2_groups_xml extends bx_dbforms2_group {
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'xml';
        $this->attributes['isxml'] = TRUE;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLValue() {
        
        $dom = new DOMDocument();
        $dom->loadXML($this->value);
        
        $value = '<xml>';        
        foreach($this->fields as $field) {
            $valueNS = $dom->getElementsByTagName($field->name);
            if($valueNS->length > 0) {
                $field->setValue($valueNS->item(0)->textContent);
                $value.= "<$field->name>";
                // these values need double encoding because they're parsed as xml 
                // on the client side
                $value.= htmlspecialchars(htmlspecialchars($field->getSQLValue()));
                $value.= "</$field->name>";
            }
        }
        $value .= '</xml>';
        
        return $value;
    }

}

?>
