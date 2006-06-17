<?php
/**
 * simple textfield patForms element that builds and validates text input fields.
 * 
 * @access		protected
 * @package		patForms
 * @subpackage	patForms_Element
 * @author		Sebastian Mordziol <argh@php-tools.net>
 */

/**
 * simple textfield patForms element that builds and validates text input fields.
 * 
 * @access		protected
 * @package		patForms
 * @subpackage	patForms_Element
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 */
class patForms_Element_Wysiwyg extends patForms_Element_Text
{
   /**
	* Stores the name of the element - this is used mainly by the patForms
	* error management and should be set in every element class.
	* @access	public
	*/
	public $elementName = 'Wysiwyg';
    
    
    
	
   /**
	* element creation method for the 'HTML' format in the 'default' form mode.
	*
	* @access	public
	* @param	mixed	value of the element
	* @return	mixed	$element	The element, or false if failed.
	*/
	function serializeHtmlDefault( $value )
	{
        
        
        if ($id = $this->getId()) {
                $this->setId("wysiwyg_".$id);
        } else {
                
               $GLOBALS['_patForms']['elementCounter']++;
               $this->setId("wysiwyg_".$$GLOBALS['_patForms']['elementCounter']);
        }
		return parent::serializeHtmlDefault($value);
	}
    
}

?>
