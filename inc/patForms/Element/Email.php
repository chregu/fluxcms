<?php
/**
 * simple textfield patForms element that builds and validates text input fields.
 * 
 * $Id$
 *
 * @access		protected
 * @package		patForms
 * @subpackage	patForms_Element
 * @author		Sebastian Mordziol <argh@php-tools.net>
 */
 
/**
 * simple textfield patForms element that builds and validates text input fields.
 * 
 * $Id$
 *
 * @access		protected
 * @package		patForms
 * @subpackage	patForms_Element
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @todo		password fields should not display value for security reasons
 */
class patForms_Element_Email extends patForms_Element_String
{
  
    
    function __construct( $format = false ) {
        parent::__construct($format);
         $this->validatorErrorCodes['C'][99] = 'Email adress is not valid.';
        
    }
   /**
	* validates the element.
	*
	* @access	public
	* @param	mixed	value of the element
	* @return	bool	$isValid	True if element could be validated, false otherwise.
	*/
	function validateElement( $value )
	{
        
        if (!parent::validateElement($value)) {
            return false;
            
        }
       
        
        if (!preg_match("#.@.+\..+#",$value)) {
            $this->addValidationError( 99 );
		return false;

        }

	return true;
	
	}
}
?>
