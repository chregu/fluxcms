<?php
/**
 * patForms renderer base class - extend this to create your own renderers.
 *
 * $Id$
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Renderer
 */

/**
 * patForms renderer base class - extend this to create your own renderers.
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Renderer
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 */
class patForms_Renderer
{
   /**
	* method called by patForms to retrieve the rendered form content.
	*
	* @access	public
	* @param	object	&$patForms	Reference to the patForms object
	*/
	function render( &$patForms )
	{
		// your code
	}
}
?>