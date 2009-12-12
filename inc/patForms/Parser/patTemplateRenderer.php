<?PHP
/**
 * Renderer based on patForms_Parser and patTemplate
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
 * Renderer based on patForms_Parser and patTemplate
 *
 * Use this parser, if you want to use the forms together
 * with patTemplate
 *
 * Possible arguements to renderForm():
 * - template : name of the template (_not_ filename) in which the elements will be added
 * - errorTemplate : name of the template to which the error messages will be added (will be repeated, if more than one error occured)
 * - errorTemplateContainer : name of the template which contains the errorTemplate. If errors occured, its visibility will be set to visible
 *
 * @author		Stephan Schmidt <s.schmidt@metrix.de>
 * @package		patForms
 * @subpackage	Parser
 * @license		LGPL
 * @copyright	PHP Application Tools <http://www.php-tools.net>
 * @version		1.0
 */
class patForms_Parser_patTemplateRenderer extends patForms_Parser
{
   /**
    * patTemplate object
	* @access	private
	*/
	var $_tmpl	=	null;
	
   /**
    * checks whether errors have been rendered or not
	* @access	private
	*/
	var $errorsRendered = array();
	
   /**
    * set the reference to the patTemplate object
	*
	* @access	public
	* @param	object	patTemplate object
	*/
	function setTemplate( &$tmpl )
	{
		$this->_tmpl	=	&$tmpl;
	}

   /**
	* gathers serialized data from all elements and replaces them in the outputFile.
	*
	* @access	public
	* @param	object	&$patForms			Reference to the patForms object
	* @param	mixed	$args				optional arguments
	* @return	string	$html				HTML code
	*/
	function render( &$patForms, $args = null )
	{
		$serializedElements	=	array();
		$elementAttribs		=	array();
		
		$cnt	=	count( $patForms->elements );
		for( $i=0; $i < $cnt; $i++ )
		{
			// first, serialize the element as this also initializes the attribute collection.
			$serialized	=	$patForms->elements[$i]->serialize();
			if( $serialized === false )
			{
				patErrorManager::raiseWarning(
					PATFORMS_PARSER_ERROR_ELEMENT_NOT_SERIALIZEABLE, 
					'Element \''.get_class( $patForms->elements[$i] ).'\' could not return serialized data.' 
				);
				continue;
			}
			$elName							=	$patForms->elements[$i]->getName();
			$serializedElements[$elName]	=	$serialized;
			$elementAttribs[$elName]		=	$patForms->elements[$i]->getAttributes();
		}
		
		// no template has been specified => use the default
		if( $this->_tmpl == null )
		{
			$this->_tmpl	=	&patForms_Parser::getStaticProperty( 'tmpl' );
		}

		// check, whether the file has been loaded
		if( !$this->_tmpl->exists( $args['template'] ) )
		{
			$this->_tmpl->readTemplatesFromFile( $this->_outputFile );
		}

		foreach( $serializedElements as $name => $element )
		{
			$this->_tmpl->addVar( $args['template'], sprintf( $this->_placeholder, $name ), $element );

			// copy the attribute collection
			$tmplVars = $elementAttribs[$name];
			
			// remove any arrays in the variables to add, as that could 
			// lead to problems with the patTemplate output.
			foreach( $tmplVars as $key => $val )
				if( is_array( $val ) )
					$tmplVars[$key] = '';
			
			// add the attribute collection as vars to the template
			$this->_tmpl->addVars( $args['template'], $tmplVars, $name.'_' );
		}

		$this->_tmpl->addVar( $args['template'], sprintf( $this->_placeholder_form_start, $patForms->getName() ), $patForms->serializeStart() );
		$this->_tmpl->addVar( $args['template'], sprintf( $this->_placeholder_form_end, $patForms->getName() ), $patForms->serializeEnd() );


		if( !isset( $args['errorTemplate'] ) )
		{
			return	true;
		}
		
		return	$this->_renderErrors( $patForms, $args );
	}

   /**
    * render the errors
	*
	* @access	private
	* @todo		check for special '__form' element
	*/
	function _renderErrors( &$patForms, $args = null )
	{
		if( isset( $this->errorsRendered[$patForms->getName()] ) )
		{
			return true;
		}
	
		/**
		 * render the errors
		 */
		if( $patForms->isSubmitted() && !$patForms->validateForm() )
		{
			if( isset( $args['errorTemplateContainer'] ) )
			{
				$this->_tmpl->setAttribute( $args['errorTemplateContainer'], 'visibility', 'visible' );
			}
			$validationErrors	=	$patForms->getValidationErrors();

			foreach( $validationErrors as $fieldName => $errors )
			{
				if( empty( $errors ) )
				{
					continue;
				}
				
				$field =& $patForms->getElement( $fieldName );

				// workaround for patTemplate Bug! - an array in the
				// added attribute collection could lead to the
				// template to be repeated too many times.
				$atts	=	$field->getAttributes();
				foreach( $atts as $key => $value )
				{
					if( is_array( $atts[$key] ) )
					{
						unset( $atts[$key] );
					}
				}
				
				$this->_tmpl->addVars( $args['errorTemplate'], $atts, 'FIELD_' );
				
				foreach( $errors as $error )
				{
					$error['field'] = $fieldName;
					$this->_tmpl->addVars( $args['errorTemplate'], $error, 'ERROR_' );
					$this->_tmpl->parseTemplate( $args['errorTemplate'], 'a' );
				}
			}
		}
		
		$this->errorsRendered[$patForms->getName()] = true;
		
		return	true;
	}
	
   /**
	* get the placeholder for an element
	*
	* @access	protected
	* @param	string		element name
	* @param	string		name of the placeholder template
	* @return	string		placeholder
	*/
	function _getPlaceholderForElement( $element, $template = 'placeholder' )
	{
		// adjust the case
		switch( $this->_placeholder_case )
		{
			case 'upper':
				$element	=	strtoupper( $element );
				break;
			case 'lower':
				$element	=	strtolower( $element );
				break;
			default:
				break;
		}
		
		return	sprintf( '{'.$this->{'_'.$template}.'}', $element );
	}

   /**
	* get the placeholder for a form tag
	*
	* @access	protected
	* @param	string		name of the form
	* @param	string		type (start|end)
	* @return	string		placeholder
	*/
	function _getPlaceholderForForm( $form, $type )
	{
		// adjust the case
		switch( $this->_placeholder_case )
		{
			case 'upper':
				$form	=	strtoupper( $form );
				break;
			case 'lower':
				$form	=	strtolower( $form );
				break;
			default:
				break;
		}

		$template	=	'_placeholder_form_'.$type;
		return	sprintf( '{'.$this->$template.'}', $form );
	}
}
?>