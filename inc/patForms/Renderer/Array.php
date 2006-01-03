<?php
/**
 * patForms array renderer class.
 *
 * $Id$
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Renderer
 */

/**
 * error definition: renderer base class file (renderers/_base.php) could not be found.
 */									
define( "PATFORMS_ERROR_RENDERER_SERIALIZEAZION_FAILED", 2110 );
 
/**
 * patForms array renderer -  gathers serialized data from all elements, 
 * and returns it along with all attributes in a handy array that can directly 
 * be added to a template to display the form.
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Renderer
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 * @todo		add javascript support
 */
class patForms_Renderer_Array extends patForms_Renderer
{
   /**
	* gathers serialized data from all elements, and returns it along with all
	* attributes in a handy array that can directly be added to a template to
	* display the form.
	*
	* @access	public
	* @param	object	&$patForms			Reference to the patForms object
	* @return	string	$serializedElements	The list with elements.
	*/
	function render( &$patForms )
	{
		$serializedElements	=	array();
		
		$elements	=&	$patForms->getElements();
		
		$cnt	=	count( $elements );
		for( $i=0; $i < $cnt; $i++ )
		{
			// first, serialize the element as this also initializes the attribute collection.
			$serialized	=	$elements[$i]->serialize();
			if( $serialized === false )
			{
				patErrorManager::raiseWarning(
					PATFORMS_ERROR_RENDERER_SERIALIZEAZION_FAILED, 
					"Element '".get_class( $elements[$i] )."' could not return serialized data." 
				);
				
				continue;
			}
			
			// now get the attributes
			$meta	=	$elements[$i]->getAttributes();
			$meta["element"]	=	$serialized;
			
			array_push( $serializedElements, $meta );
		}
		
		return $serializedElements;
	}
}
?>