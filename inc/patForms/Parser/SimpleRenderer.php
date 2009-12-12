<?PHP
/**
 * Renderer based on patForms_Parser
 *
 * This class can be used as a parser that is also
 * a renderer. It makes it quite easy to create working
 * forms from a form template
 *
 * $Id$
 *
 * @author		Stephan Schmidt <s.schmidt@metrix.de>
 * @package		patForms
 * @subpackage	Parser
 * @license		LGPL
 * @copyright	PHP Application Tools <http://www.php-tools.net>
 */
 
/**
 * Renderer based on patForms_Parser
 *
 * This class can be used as a parser that is also
 * a renderer. It makes it quite easy to create working
 * forms from a form template
 *
 * @author		Stephan Schmidt <s.schmidt@metrix.de>
 * @package		patForms
 * @subpackage	Parser
 * @license		LGPL
 * @copyright	PHP Application Tools <http://www.php-tools.net>
 * @version		1.0
 */
class patForms_Parser_SimpleRenderer extends patForms_Parser
{
   /**
	* gathers serialized data from all elements and replaces them in the outputFile.
	*
	* @access	public
	* @param	object	&$patForms			Reference to the patForms object
	* @param	mixed	$args				optional arguments
	* @return	string	$html				HTML code
	*
	* @todo		build the correct placeholders!
	*/
	function render( &$patForms, $args = null )
	{
		$serializedElements	=	array();
		
		$cnt	=	count( $patForms->elements );
		for( $i=0; $i < $cnt; $i++ )
		{
			// first, serialize the element as this also initializes the attribute collection.
			$serialized	=	$patForms->elements[$i]->serialize();
			if( $serialized === false )
			{
				patErrorManager::raiseWarning(
					PATFORMS_PARSER_ERROR_ELEMENT_NOT_SERIALIZEABLE, 
					"Element '".get_class( $patForms->elements[$i] )."' could not return serialized data." 
				);
				continue;
			}
			$serializedElements[($patForms->elements[$i]->getName())]	=	$serialized;
		}
		

		$html	=	$this->getHTML();
		foreach( $serializedElements as $name => $element )
		{
			$varName	=	sprintf( "{PATFORMS_ELEMENT_%s}", strtoupper( $name ) );
			$html		=	str_replace( $varName, $element, $html );
		}

		$name		=	$patForms->getName();
		
		$varName	=	sprintf( "{PATFORMS_FORM_%s_START}", strtoupper( $name ) );
		$html		=	str_replace( $varName, $patForms->serializeStart(), $html );
		
		$varName	=	sprintf( "{PATFORMS_FORM_%s_END}", strtoupper( $name ) );
		$html		=	str_replace( $varName, $patForms->serializeEnd(), $html );

		return	$html;
	}
}
?>