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
class bx_dbforms2_config {

    /**
     *  Config file as a DOM-object
     *  @var dom
     */
    protected $dom = NULL;

    /**
     *  XPath-object to query the config file
     *  @var xpath
     */
    protected $xpath = NULL;
    
    /**
     *  Name of the currently loaded form
     *  @var name
     */
    protected $name = '';
    
    /**
     *  constructor
     *
     *  @param  string $name Name of the form
     *  @access public
     */
    public function __construct($name = NULL) {
        
        $this->dom = new DOMDocument();
        if(isset($name)) {
            $this->load($name);
        } 
    }
    
    /**
     *  Loads a form configuration by its name. The default location for
     *  form configuration files is BX_PROJECT_DIR/dbforms2/formname.xml
     *
     *  @param  string $name Name of the form
     *  @access public
     *  @return boolean TRUE on success, FALSE otherwise
     *                    
     */
    public function load($name) {
        $this->name = $name;
        $file = $this->getConfigFileByName($name);
        return $this->loadFromFile($file);
    }
    
    /**
     *  Loads a form configuration from a file.
     *
     *  @param  string  $file Name of the configuration file
     *  @access public
     *  @return boolean TRUE on success, FALSE otherwise
     */
    protected function loadFromFile($file) {

        if(!file_exists($file)) {
            throw new PopoonFileNotFoundException($file);
        }
        
        if(@$this->dom->load($file)) {
            $this->xpath = new DOMXPath($this->dom);
            // register dbforms2 namespace
            $this->xpath->registerNamespace('dbform', 'http://bitflux.org/dbforms2/1.0');
            return TRUE;
        } else {
            throw new PopoonXMLParseErrorException($file);
        }
        return FALSE;
    }
    
    /**
     *  Returns the name of the configuration file which corresponds to
     *  a form name.
     *
     *  @param  string $name Name of the form
     *  @access public
     *  @return string Configuration file name
     */

    public function getConfigFileByName($name) {
        return BX_PROJECT_DIR."dbforms2/$name.xml";
    }
    
    /**
     *  Returns an array containing all fields of the current form.
     *
     *  @access public
     *  @return type descr
     */
    public function getFields($fieldsNode, $parentForm) {
        $fields = array();
        // get nodeset which contains all fields of the current form
        $fieldsNS = $this->xpath->query('dbform:field|dbform:group|dbform:nofield', $fieldsNode);

        foreach($fieldsNS as $field) {
            $type = $field->getAttribute('type');
            $name = $field->getAttribute('name');
            
            if($field->localName == 'field') {
                $fieldInstance = $this->getFieldInstance($type, $name);
            } else if($field->localName == 'group') {
                $fieldInstance = $this->getGroupInstance($type, $name);
            } else if($field->localName == 'nofield') {
                $fieldInstance = $this->getNofieldInstance($type, $name);
            }
            
            if($fieldInstance instanceof bx_dbforms2_field) {
                $fieldInstance->parentForm = $parentForm;
                
                $attributeSet = $fieldInstance->getConfigAttributes();
                $attributes = $this->getNodeAttributes($field, $attributeSet);
                
                $fieldInstance->setAttributes($attributes);
                
                // check if this field has values from the config file
                if($fieldInstance->hasConfigValues()) {
                    $fieldInstance->setValues($this->getFieldValues($field));
                }
                if(($default = $this->getDefaultFieldValue($field)) !== NULL) {
                    $fieldInstance->setDefaultValue($default);
                }

                if($field->localName == 'field') {
                    // check if this field has a live select
                    $lsNS = $this->xpath->query('dbform:liveselect', $field);
                    if($lsNS->length > 0) {
                        $lsNode = $lsNS->item(0);
                        $liveSelect = new bx_dbforms2_liveselect();
                        
                        $liveSelect->nameField = $lsNode->getAttribute('namefield');
                        $liveSelect->whereFields = $lsNode->getAttribute('wherefields');
                        $liveSelect->where = $lsNode->getAttribute('where');
                        $liveSelect->idField = $lsNode->getAttribute('idfield');
                        $liveSelect->tableName = $lsNode->getAttribute('table');
                        $liveSelect->orderBy = $lsNode->getAttribute('orderby');
                        $liveSelect->limit = $lsNode->getAttribute('limit');
                        if(!$liveSelect->limit)
                            $liveSelect->limit  = 20;
                            
                        $liveSelect->getMatcher = $lsNode->getAttribute('getmatcher');
                        
                        $fieldInstance->liveSelect = $liveSelect;
                    }
                } else if($field->localName == 'group') {
                    $fieldInstance->setFields($this->getFields($field));
                
                } else if($field->localName == 'nofield') {
					$fieldInstance->setValue($field->getAttribute('value'));				
                }
                
                $fields[$name] = $fieldInstance;
            } else {
                throw new Exception("Cannot instanciate $name ($type)"); 
            }
        }
        
        return $fields;
    
    }
    
    /**
     *  Returns all values for the given field as an array.
     *
     *  @param  DOMNode $fieldNode Node of the field
     *  @access protected
     *  @return array Field values
     */
    protected function getFieldValues($fieldNode) {
        $values = array();
        
        $valuesNS = $this->xpath->query('dbform:datasource', $fieldNode);
        foreach ($valuesNS as $valueNode) {
            $type = $valueNode->getAttribute("type");
            $className = 'bx_dbforms2_datasource_'.$type;
            $ds =  call_user_func(array( $className, 'getInstance'));
            $args = array();
            foreach ($valueNode->attributes as $attr) {
                   $args[$attr->name] = $attr->value;
            }
            if ($valueNode->firstChild) {
                $childValue = $valueNode->firstChild->nodeValue;
            } else {
                $childValue = null;
            }
            
            $values = $ds->getValues($args,$childValue);
             
        }
        
        $valuesNS = $this->xpath->query('dbform:value', $fieldNode);
        foreach ($valuesNS as $valueNode) {
            $values[$valueNode->getAttribute('name')] = $valueNode->textContent;
        }
        
        return $values;
    }
    
    /**
     *  Returns the default value for the given field.
     *
     *  @param  DOMNode $fieldNode Node of the field
     *  @access protected
     *  @return string Default field value
     */
    protected function getDefaultFieldValue($fieldNode) {
        $fieldName = $fieldNode->getAttribute('name');
        
        if($fieldNode->hasAttribute('default'))
            return $this->replaceByRequestVar($fieldName, $fieldNode->getAttribute('default'));
            
        $valueNS = $fieldNode->getElementsByTagName('value');
        
        if($valueNS->length > 0)  
            return $this->replaceByRequestVar($fieldName, $valueNS->item(0)->textContent);
            
        return $this->replaceByRequestVar($fieldName, '');
    }
    
    /**
     *  Replaces the given value by the request variable referenced in fieldName. If
     *  the request variable is NULL, the passed value will be returned.
     *
     *  @param  string $name Name of the request variable.
     *  @param  string $value Value to be replaced.
     *  @access protected
     *  @return string Value of the request variable or the passed value
     */
    protected function replaceByRequestVar($name, $value) {
        if(isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }
        return $value;
    }

    /**
     *  Returns a form instance for the current form.
     *
     *  @access public
     *  @return object Form object
     */
    public function getForm() {
        $fieldsNS = $this->xpath->query("/dbform:form/dbform:fields");
        $fieldsNode = $fieldsNS->item(0);

        $form = new bx_dbforms2_form();
        $form->fields = $this->getFields($fieldsNode, $form);
        $form->name = $this->name;
        $form->tableName = $this->getTableName($fieldsNode);
        $form->tablePrefix = $this->getTablePrefix($fieldsNode);
        $form->title = $this->getFormTitle($fieldsNode);

        $attributeSet = $form->getConfigAttributes();
        $attributes = $this->getNodeAttributes($fieldsNode, $attributeSet);
        $form->attributes = $attributes;
        
        $form->jsHrefs = $this->getJSHrefs();
        
        return $form;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getChooser() {
        
        $chooserNS = $this->xpath->query("/dbform:form/dbform:chooser");
        $chooserNode = $chooserNS->item(0);

        $chooser = new bx_dbforms2_liveselect();

        $chooser->nameField = $chooserNode->getAttribute('namefield');
        $chooser->whereFields = $chooserNode->getAttribute('wherefields');
        $chooser->where = $chooserNode->getAttribute('where');
        $chooser->limit = $chooserNode->getAttribute('limit');

        if(!$chooser->limit)
            $chooser->limit = 20;

        $chooser->orderBy = $chooserNode->getAttribute('orderby');
		$chooser->getMatcher = $chooserNode->getAttribute('getmatcher');
		$chooser->notNullFields = $chooserNode->getAttribute('notnullfields');
        $chooser->tableName = $this->getTableName();
        $chooser->tablePrefix = $this->getTablePrefix();
        
        $chooser->setLeftJoin($chooserNode->getAttribute('leftjoin'));
        
        return $chooser;
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getFormTitle($fieldsNode = NULL) {
        if(!isset($fieldsNode)) {
            $fieldsNS = $this->xpath->query("/dbform:form/dbform:fields");
            $fieldsNode = $fieldsNS->item(0);
        }
        if($fieldsNode && $fieldsNode->parentNode->hasAttribute('title'))
            return $fieldsNode->parentNode->getAttribute('title');
        
        return '';
    }
    
    /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getTableName($fieldsNode = NULL) {
        if(!isset($fieldsNode)) {
            $fieldsNS = $this->xpath->query("/dbform:form/dbform:fields");
            $fieldsNode = $fieldsNS->item(0);
        }
        
        if($fieldsNode && $fieldsNode->hasAttribute('table'))
            return $fieldsNode->getAttribute('table');
        
        return FALSE;
    }
    
       /**
     *  xx
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function getTablePrefix($fieldsNode = NULL) {
        if(!isset($fieldsNode)) {
            $fieldsNS = $this->xpath->query("/dbform:form/dbform:fields");
            $fieldsNode = $fieldsNS->item(0);
        }
        if($fieldsNode && $fieldsNode->hasAttribute('tablePrefix')) {
            return $fieldsNode->getAttribute('tablePrefix');
        }
        return  $GLOBALS['POOL']->config->getTablePrefix();
    }
    
    /**
     *  Returns an instance of the given field type.
     *
     *  @param  string $field Type of the field
     *  @param  string $name Name of the field
     *  @access protected
     *  @return object Field instance on succes, FALSE otherwise
     */
    protected function getFieldInstance($field, $name) {
        if($field == "nofield") {
            // "string" becomes "bx_dbforms2_string" because it's no field ;) "bx_dbforms2_nofield"
            $class = "bx_dbforms2_$field";
        }else {
            // "string" becomes "bx_dbforms2_fields_string"
            $class = "bx_dbforms2_fields_$field";
        }

        return new $class($name);
    }
    
    /**
     *  Returns an instance of the given group type.
     *
     *  @param  string $field Type of the field
     *  @param  string $name Name of the field
     *  @access protected
     *  @return object Field instance on succes, FALSE otherwise
     */
    protected function getGroupInstance($field, $name) {
        $class = "bx_dbforms2_groups_$field";
        return new $class($name);
    }
	
    /**
     *  Returns an instance of the given nofield type.
     *
     *  @param  string $field Type of the element
     *  @param  string $name Name of the element
     *  @access protected
     *  @return object Field instance on succes, FALSE otherwise
     */
    protected function getNofieldInstance($field, $name) {
        $class = "bx_dbforms2_nofield_$field";
        return new $class($name);
    }
    
    /**
     *  Returns an array with all attributes for the given field node and
     *  attribute set.
     *
     *  @param  DOMElement $node DOM-node of the field
     *  @param  array $attributeSet Attribute set
     *  @access protected
     *  @return array Array containing all field attributes
     */
    protected function getNodeAttributes($node, $attributeSet = NULL) {
        $attributes = array();
        foreach($attributeSet as $attribute => $type) {
            if($node->hasAttribute($attribute)) {
                $value = $node->getAttribute($attribute);
                
                if($type === 'bool' || $type === 'boolean') {
                    $value = strtolower($value) === 'true' ? TRUE : FALSE;
                } 

                $attributes[$attribute] = $value;
            }
        }
        return $attributes;
    }
    
    /**
     *  Returns an array containing all javascript hrefs defined for a form.
     *
     *  @access public
     *  @return array Array with hrefs
     */
    protected function getJSHrefs() {
        $hrefs = array();
        
        $jsNS = $this->xpath->query("/dbform:form/dbform:javascript");
        foreach($jsNS as $jsNode) {
            if($jsNode->hasAttribute('src')) {
                $hrefs[] = $jsNode->getAttribute('src');
            }
        }
        return $hrefs;
    }
    
    
}

?>
