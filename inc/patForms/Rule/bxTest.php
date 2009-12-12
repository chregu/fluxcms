<?php
/**
* patForms Rule ConditionalRequired
*
* $Id: ConditionalRequired.php 3041 2004-11-25 15:26:07Z philipp $
*
* @package  patForms
* @subpackage Rules
*/

/**
* patForms Rule ConditionalRequired
*
* This rule can be used to set the status of
* some elements to required depending on the value
* of another element.
*
* It has to be applied prior to validating the form.
*
* @package  patForms
* @subpackage Rules
* @author  Stephan Schmidt <schst@php-tools.net>
* @license  LGPL, see license.txt for details
* @link  http://www.php-tools.net
*/
class patForms_Rule_bxTest extends patForms_Rule
{
    public $validatorErrorCodes  =   array(
    "C" => array(
    1 => "The field '[FIELD]' has not value '[VALUE]'",
    ),
    );
    
    
    function initFormRule($filter,$ruleNode) {
        foreach($filter->getXPathNodes("forms:condition[@field != '' and @value != '']", $ruleNode) as $condNode) {
            $cField = $condNode->getAttribute('field');
            $cValue = $condNode->getAttribute('value');
            
            $this->addCondition($cField, $cValue);
        }
        
        
    }
    /**
    * fields that will be required
    * @access private
    * @var  array
    */
    var $_requiredFields = array();
    
    /**
    * conditions
    * @access private
    * @var  array
    */
    var $_conditions  = array();
    
    /**
    * add a condition
    *
    * @access public
    * @param string condition field name
    * @param mixed condition value
    */
    function addCondition( $field, $value )
    {
        $this->_conditions[$field] = $value;
    }
    
    /**
    * method called by patForms or any patForms_Element to validate the
    * element or the form.
    *
    * @access public
    * @param object patForms form object
    */
    function applyRule( &$form, $type = PATFORMS_RULE_BEFORE_VALIDATION )
    {
        $error = false;
        foreach( $this->_conditions as $field => $value )
        {
            $el  = &$form->getElement( $field );
            $val = $el->getValue();
            if( $val != $value )
            {
                $this->addValidationError(1,array("field"=>$field,"value"=>$value));
                $error = true;
            }
        }
        if ($error) {
            return false;
        }
        return true;
    }
}
?>