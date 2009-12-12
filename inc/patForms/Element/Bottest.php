<?php
/**

 */
class patForms_Element_Bottest extends patForms_Element_String
{
   /**
	* Stores the name of the element - this is used mainly by the patForms
	* error management and should be set in every element class.
	* @access	public
	*/
	public $elementName = 'Bottest';
    
    function validateElement( $value ) {
        
        if (trim($value) == '') {
            return true;
        }
        return false;
    }
 
    
}

?>
