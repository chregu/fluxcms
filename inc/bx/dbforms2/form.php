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
class bx_dbforms2_form {

    /**
     *  descr
     *  @var var
     */
    protected $members = array (
        'fields' => array(),
        'name' => '',
        'title' => '',
        'tableName' => '',
        'tablePrefix' => '',
        'idField' => 'id',
        'currentID' => 0,
        'attributes' => array(),
        'jsHrefs' => array(),
        'queryMode' => bx_dbforms2::QUERYMODE_UPDATE,
        'eventHandlers' => array(),
    );
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function __set($member, $value) {
        if(isset($this->members[$member])) 
            $this->members[$member] = $value;
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function __get($member) {
        if(isset($this->members[$member]))
            return $this->members[$member];
        
        return NULL;
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function serializeToDOM() {
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('form'));
        
        $dom->documentElement->setAttribute('name', $this->name);
        $dom->documentElement->setAttribute('title', $this->title);
        
        // append all attributes
        foreach($this->attributes as $name => $value) {
            $dom->documentElement->setAttribute($name, $value);
        }
        
        // serialize all fields
        $fieldsNode = $dom->createElement('fields');
        foreach($this->fields as $field) {
            $fieldsNode->appendChild($field->serializeToDOMNode($dom));
        }
        $dom->documentElement->appendChild($fieldsNode);
        
        // append one script node for every javascript file the form should include
        foreach($this->jsHrefs as $jshref) {
            $scriptNode = $dom->createElement('script');
            $scriptNode->setAttribute('src', $jshref);
            $dom->documentElement->appendChild($scriptNode);
        }
        
        return $dom;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getFieldByName($name) {
        if (is_array($this->fields)) {
            foreach($this->fields as $field) {
                if($field instanceof bx_dbforms2_field && ($field->name == $name)) {
                    return $field;
                }
            }
        }
    }
    
    /**
     *  Sets the values of all fields the form has.
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function setValues($values) {
        foreach($this->fields as $field) {
            if(isset($values[$field->name])) {
                $field->setValue($values[$field->name]);
            }
        }
    }
    
    /**
     *  Validates the form - not yet implemented.
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function validate() {
        return TRUE;
    }
    
    /**
     *  Returns an array containing all attributes which the form can handle.
     *
     *  Currently supported data types: string, bool
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        return array(
            'onsavejs' => 'string',
            'onsavephp' => 'string',
            'alternate' => 'string',
            'xsl'       => 'string'
        );
    }

    public function callEventHandlers($event) {
        if(!empty($this->eventHandlers['php'][$event])) {
            foreach($this->eventHandlers['php'][$event] as $handler) {
                if (strpos($handler, "::") > 0) {
                    list($class, $function) = explode ("::", $handler);
                    call_user_func(array($class, $function), $this);
                } else {
                    call_user_func($handler, $this);
                }
            }
        }
    }
    
}

?>
