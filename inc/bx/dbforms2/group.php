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
class bx_dbforms2_group extends bx_dbforms2_field {
    /**
     *  All fields of this group.
     *  @var fields
     */
    protected $fields = array();
    
    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName = 'group';
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function setFields($fields) {
        $this->fields = $fields;
    }
    
    /**
     *  Serializes the field to a DOM node.
     *
     *  @param  object $dom DOM object to be used to generate the node.
     *  @access public
     *  @return object DOM node
     */
    public function serializeToDOMNode($dom) {
        $node = parent::serializeToDOMNode($dom);
        
        $fieldsNode = $dom->createElement('fields');
        foreach($this->fields as $field) {
            $fieldsNode->appendChild($field->serializeToDOMNode($dom));
        }
        $node->appendChild($fieldsNode);
        
        return $node;
    }
    
}

?>
