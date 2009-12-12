<?PHP
/**
 * patForms parser that reads plain HTML files 
 * and creates a from from these
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
 * patForms parser that reads plain HTML files 
 * and creates a from from these
 *
 * @author		Stephan Schmidt <s.schmidt@metrix.de>
 * @package		patForms
 * @subpackage	Parser
 * @license		LGPL
 * @copyright	PHP Application Tools <http://www.php-tools.net>
 * @version		1.0
 */
class patForms_Parser_Html extends patForms_Parser
{
   /**
	* extract all form elements from the HTML page
	*
	* @access	public
	* @param	string		html content
	* @return	boolean
	* @todo		extract select fields
	* @todo		extract form information
	*/
    function parseString( $string )
	{
		// set default attributes fo the form
		$this->_formAttributes = array(
										'__default' => array(
															'name' => 'form'
														)
									);

		/**
		 * transform simple input fields
		 */
		$regexp	 = "/<input ([^>]+)\/?>/";
		$matches = array();
		if( preg_match_all( $regexp, $string, $matches ) )
		{
			for( $i = 0; $i < count( $matches[1] ); $i++ )
			{
				$atts = trim( $matches[1][$i] );
				if( substr( $atts, -1 ) == '/' )
					$atts	=	substr( $atts, 0, -1 );

				$atts	=	$this->_parseAttributes( $atts );
				array_change_key_case( $atts, CASE_LOWER );
				
				switch( strtolower( $atts['type'] ) )
				{
					case 'password':
						$atts['type'] = 'password';
						break;
					case 'radio':
						unset( $atts['type'] );
						$elName	=	'Radio';
						break;
					case 'checkbox':
						unset( $atts['type'] );
						$elName	=	'Switch';
						break;
					case 'text':
						unset( $atts['type'] );
						$elName	=	'String';
						break;
					case 'hidden':
						unset( $atts['type'] );
						$elName	=	'Hidden';
						break;
					case 'file':
						unset( $atts['type'] );
						$elName	=	'File';
						break;
					default:
						continue 2;
						break;
				}

				
				if( isset( $atts['patforms:type'] ) )
				{
					$elName	= $atts['patforms:type'];
					unset( $atts['patforms:type'] );
				}

				$this->addElementDefinition( $atts['name'], $elName, $atts );
				$pl = $this->_getPlaceholderForElement( $atts['name'] );
				$string = str_replace( $matches[0][$i], $pl, $string );
			}
		}

		/**
		 * transform textarea fields
		 */
		$regexp	 = "/<textarea ([^>]+)\/?>([^<]*)<\/textarea>/sm";
		$matches = array();
		if( preg_match_all( $regexp, $string, $matches ) )
		{
			for( $i = 0; $i < count( $matches[1] ); $i++ )
			{
				$atts = trim( $matches[1][$i] );
				
				$atts	=	$this->_parseAttributes( $atts );
				array_change_key_case( $atts, CASE_LOWER );

				/**
				 * input => string
				 */
				$elName	=	'Text';

				$atts['default'] = $matches[2][$i];

				$this->addElementDefinition( $atts['name'], $elName, $atts );
				$pl = $this->_getPlaceholderForElement( $atts['name'] );
				$string = str_replace( $matches[0][$i], $pl, $string );
			}
		}
        
		/**
		 * transform select fields
		 */
		$regexp	 = "/<select ([^>]+)\/?>(.*)<\/select>/smU";
		$matches = array();
		if( preg_match_all( $regexp, $string, $matches ) )
		{
			for( $i = 0; $i < count( $matches[1] ); $i++ )
			{
				$atts = trim( $matches[1][$i] );

				$atts	=	$this->_parseAttributes( $atts );
				array_change_key_case( $atts, CASE_LOWER );

				/**
				 * input => string
				 */
				$elName	=	'Enum';
				if( isset( $atts['multiple'] ) )
				{
					$elName = 'Set';
					unset( $atts['multiple'] );
				}

				$matches2 = array();
				$regexp	 = "/<option ([^>]+)\/?>([^<]*)(<\/option>)?/sm";
				$options = array();
				if( preg_match_all( $regexp, $matches[2][$i], $matches2 ) )
				{
					for( $j = 0; $j < count( $matches2[0] ); $j++ )
					{
						$opt = $this->_parseAttributes( $matches2[1][$j] );
						$opt['label'] = trim( $matches2[2][$j] );
						array_push( $options, $opt );
					}
				}
                $atts['values'] = $options;
				$this->addElementDefinition( $atts['name'], $elName, $atts );
				$pl = $this->_getPlaceholderForElement( $atts['name'] );
				$string = str_replace( $matches[0][$i], $pl, $string );
			}
        }

		$this->_html = $string;
		
		return true;
	}

   /**
	* write a patForms template after extracting the elements from the
	* template.
	*
	* This allows you to later add additional information.
	*
	* @access	public
	* @param	string		filename
	* @param	string		namespace
	* @return	boolean
	*/	
	function writeFormTemplate( $file, $ns = 'patForms' )
	{
		$patForms = &$this->getForm();
		$cnt	=	count( $patForms->elements );
		for( $i=0; $i < $cnt; $i++ )
		{
			// first, serialize the element as this also initializes the attribute collection.
			$serialized	=	trim( $patForms->elements[$i]->toXML( $ns ) );
			
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

		$this->_writeToFile( $this->_adjustFilename( $file ), $html );
		return true;
	}

   /**
	* parse an attribute string and build an array
	*
	* @access	private
	* @param	string	attribute string
	* @param	array	attribute array
	*/
	function _parseAttributes( $string )
	{
		$string = trim( $string );
		//	Check for trailing slash, if tag was an empty XML Tag
		if( substr( $string, -1 ) == "/" )
			$string	=	trim( substr( $string, 0, strlen( $string )-1 ) );

		$pairs = explode( ' ', $string );
		for	( $i = 0; $i < count($pairs); $i++ )
		{
			$pair = explode( '=', trim( str_replace( '"', '', $pairs[$i] ) ) );

			if( count( $pair ) == 1 )
 				$pair[1] = 'yes';
			
			$attributes[strtolower( $pair[0]) ]	= $pair[1];
		}
		return	$attributes;
	}
}
?>