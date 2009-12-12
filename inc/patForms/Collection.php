<?php
/**
 * patForms collection
 *
 * $Id$
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Rules
 */

/**
 * patForms collection
 *
 * This is used as a container for several patForms objects.
 *
 * @access		protected
 * @package		patForms
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 */
class patForms_Collection
{
   /**
	* forms in the collection
	*
	* @access	public
	* @var		array
	*/
	var	$forms = array();

   /**
	* create a new collection object
	*
	* @access	public
	*/
	function patForms_Collection()
	{
	}

   /**
	* add a new form to the collection
	*
	* @access	public
	* @param	object patForms
	*/
	function addForm(&$form)
	{
		$name = &$form->getName();
		$this->forms[$name] = &$form;
	}

   /**
	* check, whether the collection contains a form
	*
	* @access	public
	* @param	string				name of the form
	* @return	boolean
	*/
	function containsForm($name)
	{
		if (isset($this->forms[$name])) {
			return true;
		}
		return false;
	}

   /**
	* get a form from the collection
	*
	* @access	public
	* @param	string				name of the form
	* @return	object patForms		form object
	*/
	function &getForm($name)
	{
		if (isset($this->forms[$name])) {
			return $this->forms[$name];
		}
		$null = null;
		return $null;
	}
	
   /**
    * sets a renderer object that will be used to render
	* the form.
	*
	* @access	public
	* @param	object		&$renderer	The renderer object
	* @return	mixed		$success	True on success, patError object otherwise.
	* @see		patForms::createRenderer()
	* @uses		patForms::renderForm()
	*/
	function setRenderer( &$renderer, $args = array() )
	{
		if( !is_object( $renderer ) )
		{
			return patErrorManager::raiseError( 
				PATFORMS_ERROR_INVALID_RENDERER, 
				'You can only set a patForms_Renderer object with the setRenderer() method, "'.gettype( $renderer ).'" given.'
			);
		}
		
		foreach (array_keys($this->forms) as $formName) {
			$this->forms[$formName]->setRenderer($renderer, $args);
		}
		return true;
	}
	
	function renderForm( $args = null )
	{
		foreach (array_keys($this->forms) as $formName) {
			$this->forms[$formName]->renderForm($args);
		}
		return true;
	}
}
?>