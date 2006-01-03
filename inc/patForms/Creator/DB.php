<?php
/**
 * patForms Creator DB
 *
 * $Id$
 *
 * @package		patForms
 * @subpackage	Creator
 */

/**
 * PEAR::DB class
 */
 require_once 'DB.php';

/**
 * Error: could not connect to the database
 */
 define( 'PATFORMS_CREATOR_DB_ERROR_NO_CONNECTION', 'patForms:Creator:DB:01' );
 
/**
 * patForms Creator DB
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Creator
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 */
class patForms_Creator_DB extends patForms_Creator
{
   /**
	* Create a form from a database
	*
	* @access	public
	* @param	mixed	$db			Either a dsn or an existing DB object
	* @param	mixed	$source		Either a table name or a result
	* @param	array	$options	Any options the creator may need
	* @return	object 	$form		The patForms object, or a patError object on failure.
	* @todo		build code generator
	*/
	function &create( $db, $source, $options = array() )
	{
		if( is_string( $db ) )
		{
			$db =& DB::connect( $db );
			
			if( DB::isError( $db ) )
			{
				return patErrorManager::raiseError(
					PATFORMS_CREATOR_DB_ERROR_NO_CONNECTION,
					'Could not connect to the specified database.',
					$db->getMessage()." >> ".$db->getUserInfo()
				);
			}
		}

		$form =& patForms::createForm( null, array( 'name' => 'patForms_Creator_Form' ) );
		
		$info = $db->tableInfo( $source );

		$cntElements	=	count( $info );
		for( $i = 0; $i < $cntElements; $i++ )
		{
			$info[$i]['flags']	=	explode( ' ', $info[$i]['flags'] );

			$attribs	=	array(
									'edit'	=>	'yes',
									'title'	=>	$info[$i]['name'],
									'label'	=>	$info[$i]['name'],
									'name'	=>	$info[$i]['name']
								);
			
			switch( strtolower( $info[$i]['type'] ) )
			{
				case	'int':
					$type	=	'Number';
					break;
				case	'datetime':
					$type	=	'Number';
					break;
				case	'float':
					$type	=	'Number';
					break;
				case	'string':
					$type	=	'String';
					$attribs['maxlength']	=	$info[$i]['len'];
					break;
				default:
					$type	=	'String';
					break;
			}

			if( in_array( 'not_null', $info[$i]['flags'] ) )
			{
				$attribs['required']	=	'yes';
			}
			
			if( $type == 'Number' )
			{
				if( in_array( 'unsigned', $info[$i]['flags'] ) )
				{
					$attribs['min']	=	0;
				}
			}
			
			$form->addElement( $form->createElement( $info[$i]['name'], $type, $attribs ) );
		}
		
		return	$form;
	}

}
?>