<?php
/**
 * patForms Rule Email
 *
 * $Id$
 *
 * @package		patForms
 * @subpackage	Rules
 */

/**
 * patForms Rule Email
 *
 * Rule to check an eMail address. This can be done in three checks:
 * - syntax
 * - MX
 * - user
 *
 * The MX and user check require PEAR Net_DNS. To check the
 * user you will also need to install Net_SMTP.
 *
 * @package		patForms
 * @subpackage	Rules
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 */
class patForms_Rule_Email extends patForms_Rule
{
   /**
	* name of the rule
	*
	* @abstract
	* @access	private
	*/
	var	$ruleName = 'Email';

   /**
	* define error codes and messages for the rule
	*
	* @access	private
	* @var		array	$validatorErrorCodes
    * @todo     translate error messages
	*/
	var	$validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"Eine E-Mail Adresse darf maximal 132 Zeichen lang sein.",
			2	=>	"Die E-Mail Adresse darf keine Leerzeichen enthalten.",
			3	=>	"Die E-Mail Adresse darf keine Umlaute oder 'ß' enthalten.",
			4	=>	'Eine E-Mail-Adresse muss genau ein \'@\' enthalten.',
			5	=>	"Die E-Mail Adresse enthält ungültige Zeichen.",
			6	=>	"Die E-Mail Adresse darf keine zwei aufeinanderfolgenden Punkte enthalten.",
			7	=>	"Das Eingabefeld enthält kein gültiges Format. Bitte ändern Sie Ihre Eingabe entsprechend dem Muster [name@domainname.de].",
			
			8	=>	"Die Domain der E-Mail Adresse konnte nicht gefunden werden.",
			9	=>	"Der Mailserver verweigert die Annahme von E-Mails für die E-Mail-Adresse."
		),
	);

   /**
	* flag to indicate whether the MX server should be checked
	* 
	* @var		boolean
	* @access	private
	*/
	var $_checkMx = false;

   /**
	* flag to indicate whether the username should be checked
	* 
	* @var		boolean
	* @access	private
	*/
	var $_checkUser = false;

   /**
	* enable the MX check
	*
	* @access	public
	* @param	boolean
	*/
	function enableMxCheck($flag = true)
	{
		$this->_checkMx = $flag;
	}
	
   /**
	* enable the User check
	*
	* @access	public
	* @param	boolean
	*/
	function enableUserCheck($flag = true)
	{
		$this->_checkUser = $flag;
	}
	
   /**
	* method called by patForms or any patForms_Element to validate the
	* element or the form.
	*
	* @access	public
	* @param	object patForms	form object
	*/
	function applyRule( &$element, $type = PATFORMS_RULE_BEFORE_VALIDATION )
	{
		$value = $element->getValue();
		if (empty($value)) {
			return true;
		}
		
		if( strlen( $value ) > 132 ) {
			$this->addValidationError(1);
			return false;
		}
		
		//	check for spaces
		if( eregi( "[[:space:]]", $value ) ) {
			$this->addValidationError(2);
		}

		//	check for German umlaut
		if( eregi( "[üöäß]", $value ) ) {
			$this->addValidationError(3);
			return false;
		}

		//	check for more than one '@'
		if( substr_count( $value, '@' ) != 1 ) {
			$this->addValidationError(4);
			return false;
		}

		//	check for valid chars in email
		$validChars	=	preg_quote( "abcdefghijklmnopqrstuvwxyz1234567890@.+_-" );
		if( !preg_match( "/^[".$validChars."]+$/i", $value ) ) {
			$this->addValidationError(5);
			return false;
		}
		
		if (strstr( $value, '..' )) {
			$this->addValidationError(6);
			return false;
		}

		//	check format
		if( !eregi( "^.*[^._-]@[^-.].+\..{2,}$", $value ) ) {
			$this->addValidationError(7);
			return false;
		}

		//	check for existing mailserver
		if ($this->_checkMx || $this->_checkUser) {
			require_once 'PEAR.php';
			require_once 'Net/DNS.php';
			require_once 'Net/DNS/Resolver.php';
			
			$resolver = new	Net_DNS_Resolver();
			$domain   = substr( strchr( $value, '@' ), 1 );
			$mxResult = $resolver->send( $domain, "MX", "IN" );
			
			if( PEAR::isError( $mxResult ) || empty( $mxResult->answer ) ) {
				$this->addValidationError(8);
				return false;
			}

			//	ask mailserver, whether user exists
			if ($this->_checkUser) {
				require_once 'Net/SMTP.php';
				$found     = false;
				$mxServers = $mxResult->answer;
				$cnt       = count( $mxServers );
				for ($i = 0; $i < $cnt; $i++) {
					
					if (isset($mxServers[$i]->exchange)) {
						$smtp	=	new Net_SMTP( $mxServers[$i]->exchange );
						$smtp->connect();
						$result	=	$smtp->mailFrom( 'test@w3c.org' );
						if (PEAR::isError($result)) {
							continue;
						}
						$result	= $smtp->rcptTo( $value );
						if (PEAR::isError( $result )) {
							continue;
						}
						$found	=	true;
						break;
					}
				}

				if ($found === false) {
					$this->addValidationError(9);
					return false;
				}
			}
		}
		return true;
	}
}
?>