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
class bx_dbforms2_field {

    /**
     *  Type of the field
     *  @var type
     */
    protected $type = '';
    
    /**
     *  Name of the field
     *  @var name
     */
    public $name = '';
    
    /**
     *  The default value
     *  @var value
     */
    protected $defaultValue = NULL;
    
    /**
     *  Current value
     *  @var value
     */
    protected $value = NULL;
    
    /**
     *  Possible values
     *  @var values
     */
    protected $values = NULL;
    
    /**
     *  Field attributes
     *  @var var
     */
    protected $attributes = array(
        'descr' => '',
        'disabled' => FALSE,
    );
    
    /**
     *  Name of the tag which is used for serialization to XML.
     *  @var tagName
     */
    protected $XMLName = 'field';
    
    /**
     *  Name of the tag which is used for serialization of field values to XML.
     *  @var tagName
     */
    protected $valueXMLName = 'option';
    
    /**
     *  The live select object of this field when it has one
     *  @var liveSelect
     */
    public $liveSelect = NULL;
    

    /**
     *  Instance of the form this field belongs to
     *  @var parentForm
     */
    public $parentForm = NULL;
    
    /**
    *  Indicates whether field is ignored in sql query
    "
    *  @var nosql
    */
    public $nosql = FALSE;
    
    /**
     *  Constructor
     *
     *  @param  type  $var descr
     *  @access public
     */
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function setParentForm($parentForm) {
        $this->parentForm = $parentForm;    
    }
    
    /**
     *  Gets the current field value.
     *
     *  @access public
     *  @return mixed Current value
     */
    public function getValue() {
        return $this->value;
    }

    /**
     *  Sets the current field value.
     *
     *  @param  mixed $value New field value
     *  @access public
     */
    public function setValue($value) {
        $this->value = $value;
    }
    
    /**
     *  Sets the default field value.
     *
     *  @param  mixed $value Default field value
     *  @access public
     */
    public function setDefaultValue($value) {
        $this->defaultValue = $value;
    }
    
    /**
     *  Sets all possible values for this field.
     *
     *  @param  array $values Array containing all possible values.
     *  @access public
     */
    public function setValues($values) {
        $this->values = $values;
    }

    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getAttribute($attribute) {
        if(isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }
    }
    
    /**
     *  Set field attributes
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function setAttributes($attributes) {
        foreach($attributes as $name => $value) {
            if($value !== NULL) {
                $this->attributes[$name] = $value;
            }
        }
    }
    
    /**
     *  Returns an array containing all attributes which the field can handle.
     *
     *  Currently supported data types: string, bool
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        return array(
            'descr' => 'string', 
            'isxml' => 'bool',
            'onkeyup' => 'string',
            'nosql' => 'bool',
            'disabled' => 'bool',
        );
    }
    
    /**
     *  Whether the plugin has child nodes in the configuration.
     *
     *  @param  type  $var descr
     *  @access public
     *  @return boolean TRUE if the field has child nodes, FALSE otherwise
     */
    public function hasConfigChildNodes() {
        return FALSE;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function hasConfigvalues() {
        return FALSE;
    }
    
    /**
     *  Returns the name of the field to be used in a SQL query.
     *
     *  @access public
     *  @return string Field name
     */
    public function getSQLName() {
        return $this->name;
    }
    
    /**
     *  Returns the current value in the format it should be inserted into the db
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLValue() {
        return $this->value;
    }
    
    /**
     *  Returns an array containing all standard attributes the field has when
     *  being serialized to XML.
     *
     *  @access protected
     *  @return Array Array with attributes
     */
    protected function getStandardXMLAttributes() {
        
        if(!isset($this->attributes['descr']))
            $this->attributes['descr'] = $this->name;
            
        
        $ret =  array(
            'type' => $this->type,
            'name' => $this->name,
            'descr' => $this->attributes['descr'],
            'id' => $this->parentForm->name.'_'.$this->name,
        );
        
        if(isset($this->attributes['onkeyup'])) {
            $ret['onkeyup'] = $this->attributes['onkeyup'];    
        }

        if($this->attributes['disabled'] === TRUE) {
            $ret['disabled'] = 'disabled';    
        }
        
        return $ret;
    }
    
    /**
     *  Returns an array containing all additional attributes the field has when
     *  being serialized to XML.
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getXMLAttributes() {
        return array();
    }
    
    /**
     *  Validates the field - not yet implemented.
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function validate() {
        return TRUE;
    }
    
    /**
     *  Serializes the field to a DOM node.
     *
     *  @param  object $dom DOM object to be used to generate the node.
     *  @access public
     *  @return object DOM node
     */
    public function serializeToDOMNode($dom) {
        $node = $dom->createElement($this->XMLName);

        $attributes = array_merge($this->getStandardXMLAttributes(), $this->getXMLAttributes());
        foreach($attributes as $attr => $value) {
            $node->setAttribute($attr, $value);
        }
        
        if(!empty($this->values)) {
            foreach($this->values as $name => $disp) {
                if (is_array($disp)) {
                    $valueNode = $dom->createElement('entry');
                    bx_helpers_xml::array2Dom($disp,$dom, $valueNode ); 
                } else {
                
                    $valueNode = $dom->createElement($this->valueXMLName, $disp);
                }
                $valueNode->setAttribute('value', $name);
                
                $node->appendChild($valueNode);
            }
        }
        
        if($this->defaultValue != NULL) {
            $dvNode = $dom->createElement('default', $this->defaultValue);
            $node->appendChild($dvNode);
        }
        
        return $node;
    }
    
    /**
     *  Returns additional data of this field.
     *
     *  @param  mixed $id The currently used id.
     *  @access public
     *  @return type descr
     */
    public function getAdditionalData($id) {
        return NULL;
    }
    
    /**
     *  Executes additional Queries on this field.
     *
     *  @param  mixed $id The currently used id.
     *  @access public
     *  @return type descr
     */
    public function doAdditionalQuery($id) {
        return NULL;   
    }
    
    /**
     *   Indicates whether the SQL value of this field should be quoted
     *
     *  @access public
     *  @return boolean TRUE means quote please, FALSE the opposite
     */
    public function quoteSQLValue() {
        return TRUE;
    }
    
    /**
    *  Replaces all occurences of {tablePrefix} in the given string with the
    *  current table prefix.
    *
    *  @param  string $strIn Input string
    *  @access protected
    *  @return string
    */
    protected function replaceTablePrefix($strIn) {
        return str_replace('{tablePrefix}', $this->parentForm->tablePrefix, $strIn);
    }

}

