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
class patForms_Rule_EmailEN extends patForms_Rule_Email
{
   /**
	* name of the rule
	*
	* @abstract
	* @access	private
	*/
	var	$ruleName = 'EmailEN';

   /**
	* define error codes and messages for the rule
	*
	* @access	private
	* @var		array	$validatorErrorCodes
    * @todo     translate error messages
	*/
	var	$validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"An email address can't be longer than 132 chars.",
            2	=>	"An email address is not allowed to contain spaces.",
			3	=>	"An email address is not allowed to contain umlauts.",
			4	=>	'An email address needs exactly one \'@\' sign.',
            5	=>	"The email address contains invalid chars.",
			6	=>	"An email address is not allowed to contain more than one consecutive dot.",
			7	=>	"The inputfield contains an invalid format. Please change it [name@domainname.com].",
			8	=>	"The domain of the email address could not be found.",
			9	=>	"The mailserver rejects your email-address." 
		),
	);

  
}
?>