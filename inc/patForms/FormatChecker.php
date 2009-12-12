<?php
/**
 * patForms utility class: simple type checker class that can be used to validate
 * common data types.
 *
 * @package		patForms
 * @subpackage	patForms_Element
 * @access		public
 * @version		0.1
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 */

/**
 * patForms utility class: simple type checker class that can be used to validate
 * common data types. Use the {@link patForms_Element::validateFormat()} method to
 * use the methods included here.
 *
 * @package		patForms
 * @subpackage	patForms_Element
 * @access		public
 * @version		0.1
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 */
class patForms_FormatChecker
{
   /**
	* checks if given value is a valid email address.
	*
	* @access	public
	* @param	string	$email		The string to check
	* @return	bool	$isValid	True if string is an email address, false otherwise.	
	*/
	function is_email( $email )
	{
		$ereg	=	"^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$";

		if( !eregi( $ereg, $email ) )
		{
			return false;
		}
		
		return true;
	}
	
   /**
	* checks if given value is a valid url.
	*
	* @access	public
	* @param	string	$url		The url to check
	* @return	bool	$isValid	True if string is a valid url, false otherwise.
	*/
	function is_url( $url )
	{
		$ereg	=	"(http|ftp|https)://[-A-Za-z0-9._]+(\/([A-Za-z0-9\-\_\.\!\~\*\'\(\)\%\?]+))*/?";

		if( !eregi( $ereg, $url ) )
		{
			return false;
		}
		
		return true;
	}
}

?>