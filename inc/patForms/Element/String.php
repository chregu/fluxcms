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
class patForms_Element_String extends patForms_Element
{
   /**
	* javascript that will be displayed only once
	*
	* @access	private
	* @var		array
	*/
	public $globalJavascript	=	array(
										'html'	=>	"/* patForms::Element::String */
function pFEC_String( id )
{
	this.id = id;
}

pFEC_String.prototype.getValue	=	function()
{
	var element = document.getElementById( this.id );
	return element.value;
}
/* END: patForms::Element::String */
"
									);

   /**
	* javascript that will be displayed once per instance
	*
	* @access	private
	* @var		array
	*/
	public $instanceJavascript	=	array(
										'html'	=>	"var pfe_[ELEMENT::NAME] = new pFEC_String( '[ELEMENT::ID]' );\n"
									);

   /**
	* Stores the name of the element - this is used mainly by the patForms
	* error management and should be set in every element class.
	* @access	public
	*/
	public $elementName	=	'String';

   /**
	* the type of the element - set this to the type of element you are creating
	* if you want to use the {@link patForms_Element::element2html()} method to 
	* create the final HTML tag for your element.
	*
	* @access	public
	* @see		patForms_Element::element2html()
	*/
	public $elementType	=	array(	"html"	=>	"input" );
	
   /**
	* set here which attributes you want to include in the element if you want to use
	* the {@link patForms_Element::convertDefinition2Attributes()} method to automatically
	* convert the values from your element definition into element attributes.
	*
	* @access	protected
	* @see		patForms_Element::convertDefinition2Attribute()
	*/
	public $attributeDefinition	=	array(	
			
			'id' =>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
			),
			'name'			=>	array(	
				'required'		=>	true,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'type'			=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'text',
				'outputFormats'	=>	array( 'html' ),
			),
			'title'			=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'description'	=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array(),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'default'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array(),
			),
			'label'			=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array(),
			),
			'display'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'yes',
				'outputFormats'	=>	array(),
			),
			'edit'			=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'yes',
				'outputFormats'	=>	array(),
			),
			'required'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'yes',
				'outputFormats'	=>	array(),
			),
			'value'			=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
			),
			'style'			=>	array(	
				'required'		=>	false,
				'outputFormats'	=>	array( 'html' ),
				'format'		=>	'string',
			),
			'class'			=>	array(	
				'required'		=>	false,
				'outputFormats'	=>	array( 'html' ),
				'format'		=>	'string',
			),
			'onchange'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'onclick'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'onfocus'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'onmouseover'	=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'onmouseout'	=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'onblur'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
				'modifiers'		=>	array( 'insertSpecials' => array() ),
			),
			'accesskey'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array( 'html' ),
			),
			'position'		=>	array(	
				'required'		=>	false,
				'format'		=>	'int',
				'outputFormats'	=>	array(),
			),
			'tabindex'		=>	array(	
				'required'		=>	false,
				'format'		=>	'int',
				'outputFormats'	=>	array( 'html' ),
			),
			'maxlength'		=>	array(	
				'required'		=>	false,
				'format'		=>	'int',
				'outputFormats'	=>	array( 'html' ),
			),
			'minlength'		=>	array(	
				'required'		=>	false,
				'format'		=>	'int',
				'outputFormats'	=>	array(),
			),
			'format'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'outputFormats'	=>	array(),
			),
			'disabled'		=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'no',
				'outputFormats'	=>	array( 'html' ),
			),
			'size' => array(
				'required'		=>	false,
				'format'		=>	'int',
				'outputFormats'	=>	array( 'html' ),
			),
			'allowedtags'	=>	array(	
				'required'		=>	false,
				'format'		=>	'string',
				'default'		=>	'',
				'outputFormats'	=>	array(),
			),
		);

    /**
     *	define error codes an messages for each form element
     *
     *  @access private
     *  @var	array	$validatorErrorCodes
     */
	public $validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"This field is required, please complete it.",
			2	=>	"Value is not a string.",
			3	=>	"The value is shorter than the minimum length of [MINLENGTH].",
			4	=>	"The value is longer than the maximum length of [MAXLENGTH].",
			5	=>	"The value does not match the required input format.",
			6	=>	"The value contains some tags that are not allowed - only [ALLOWEDTAGS] are allowed here",
			7	=>	"Markup tags are not allowed.",
		),
		"de" =>	array(
			1	=>	"Pflichtfeld. Bitte vervollst�ndigen Sie Ihre Angabe.",
			2	=>	"Wert ist keine Zeichenkette.",
			3	=>	"Eingabe zu kurz, bitte geben Sie mindestens [MINLENGTH] Zeichen ein.",
			4	=>	"Eingabe zu lang, bitte geben Sie maximal [MAXLENGTH] Zeichen ein.",
			5	=>	"Der angegebene Wert entspricht nicht dem gew�nschten Eingabeformat.",
			6	=>	"Der Text enth�lt unbekannte Tags; es sind nur '[ALLOWEDTAGS]' erlaubt.",
			7	=>	"Tags sind nicht erlaubt.",
		),
		"fr" =>	array(
			1	=>	"Ce champ est obligatoire.",
			2	=>	"Pas une cha�ne de caract�res valide.",
			3	=>	"Valeur trop courte. Longueur minimum: [MINLENGTH] caract�res.",
			4	=>	"Valeur trop longue. Longueur maximum: [MAXLENGTH] caract�res.",
			5	=>	"La valeur ne correspond pas au format souhait�.",
			6	=>	"Le texte contient des balises non autoris�es - les balises autoris�es sont '[ALLOWEDTAGS]'.",
			7	=>	"Les balises ne sont pas autoris�es.",
		)
	);
		
   /**
	* element creation method for the 'HTML' format in the 'default' form mode.
	*
	* @access	public
	* @param	mixed	value of the element
	* @return	mixed	$element	The element, or false if failed.
	*/
	function serializeHtmlDefault( $value )
	{
		$this->attributes["value"]	=	$value;
		
		// handle display attribute
		if( $this->attributes['display'] == 'no' )
		{
			return $this->createDisplaylessTag( $value );
		}
		
		if( $this->attributes["edit"] == "no" )
		{
			$this->attributes['disabled']	=	'yes';
		}
		
		// create element
		return $this->toHtml();
	}
	
   /**
	* element creation method for the 'HTML' format in the 'readonly' form mode.
	* Very simple; just returns the stored element value.
	*
	* @access	public
	* @return	string	$value	The element's value
	*/
	function serializeHtmlReadonly( $value )
	{
		$tag = $this->createDisplaylessTag( $value );
		
		if( $this->attributes['display'] == 'no' )
		{
			return $tag;
		}
		
		$display = $value;
		
		// password: we don't want to display this as plain text...
		if( $this->attributes["type"] == "password" )
		{
			$display = str_repeat( "*", strlen( $value ) );
		}
		
		return $display.$tag;
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
		$required	=	false;
		$empty		=	false;
		
		// store the required flag for easy access
		if( isset( $this->attributes["required"] ) && $this->attributes["required"] == "yes" )
			$required	=	true;
		
		if( strlen( $value ) == 0 )
			$empty	=	true;
			
		if( $empty && $required )
		{
			$this->addValidationError( 1 );
			return false;
		}
			
		if( $empty && !$required )
			return true;
			
		// is it a string?
		if( !is_string( $value ) )
		{
			$this->addValidationError( 2 );
			return false;
		}
		
		// check for tags
		if( strlen( $this->attributes["allowedtags"] ) )
		{
			$allowed	=	explode( ',', $this->attributes["allowedtags"] );
			for( $i = 0; $i < count( $allowed ); ++$i )
			{
				$allowed[$i]	=	'<' . $allowed[$i] . '>';
			}
			$allowed	=	implode( '', $allowed );
			$newValue	=	strip_tags( $value, $allowed );
			if( strlen( $newValue ) != strlen( $value ) )
			{
				$this->addValidationError( 6, array( 'allowedtags' => htmlspecialchars( $allowed ) ) );
				return false;
			}
		}
		else
		{
			$newValue	=	strip_tags( $value );
			if( strlen( $newValue ) != strlen( $value ) )
			{
				$this->addValidationError( 7 );
				return false;
			}
		}

		// minlength
		if( isset( $this->attributes["minlength"] ) && strlen( $value ) < $this->attributes["minlength"] )
		{
			$this->addValidationError( 3, array( "minlength" => $this->attributes["minlength"] ) );
			return false;
		}
		
		// maxlength
		if( isset( $this->attributes["maxlength"] ) && strlen( $value ) > $this->attributes["maxlength"] )
		{
			$this->addValidationError( 4, array( "maxlength" => $this->attributes["maxlength"] ) );
			return false;
		}
		
		// format
		if( isset( $this->attributes["format"] ) && !$this->validateFormat( $value, $this->attributes["format"] ) )
		{
			$this->addValidationError( 5 );
			return false;
		}

		return true;
	}
}
?>
