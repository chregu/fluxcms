<?php
/**
 * patForms storage DB
 *
 * $Id$
 *
 * @package		patForms
 * @subpackage	Storage
 * @author		Stephan Schmidt <schst@php-tools.net>
 */

/**
 * needs PEAR::DB
 */
require_once 'DB.php';

/**
 * could not open storage container
 */
define( 'PATFORMS_STORAGE_ERROR_STORAGE_INVALID', 20000 );
 
/**
 * patForms storage DB
 *
 * Stores form data in a database.
 *
 * @access		protected
 * @package		patForms
 * @subpackage	Storage
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL, see license.txt for details
 * @link		http://www.php-tools.net
 * @todo		add error management
 */
class patForms_Storage_DB extends patForms_Storage
{
   /**
	* datasource name
	*
	* @access	private
	* @var		string
	*/
	var $_dsn;

   /**
	* table name
	*
	* @access	private
	* @var		string
	*/
	var $_table;

   /**
	* instance of PEAR::DB
	*
	* @access	private
	* @var		object
	*/
	var $_db;

   /**
	* field map
	*
	* @access	private
	* @var		string
	*/
	var $_fieldMap	=	array();

   /**
	* set the dsn and table
	*
	* @access	public
	* @param	string		datasource name
	* @param	string		table
	*/
	function setStorageLocation( $dsn, $table )
	{
		$this->_dsn		=	$dsn;
		$this->_table	=	$table;
	}

   /**
	* set the field map
	*
	* The field map is an associative array, that defines how
	* the form elements (key) map to fields in the
	* table (value)
	*
	* @access	public
	* @param	array		field map
	*/
	function setFieldMap( $fieldMap )
	{
		$this->_fieldMap	=	$fieldMap;
	}

   /**
	* get an entry
	*
	* This tries to find an entry in the storage container
	* that matches the current data that has been set in the
	* form and populates the form with the data of this
	* entry
	*
	* @access	public
	* @param	object patForms		patForms object that should be stored
	* @return	boolean				true on success
	*/
	function loadEntry( &$form )
	{
		$values  = $form->getValues();
		$primary = $this->getPrimary( $values );

		/**
		 * entry does not exists
		 */
		if( !$data = $this->_entryExists( $primary ) )
			return array();

		$values	=	$this->_unmapFields( $data );
			
		$form->setValues( $values );
		return true;
	}

   /**
	* adds an entry to the storage
	*
	* The entry will be appended at the end of the file.
	*
	* @abstract
	* @param	object patForms		patForms object that should be stored
	* @return	boolean				true on success
	*/
	function _addEntry( &$form )
	{
		$values = $form->getValues();
		
		$this->_prepareConnection();
		$values	=	$this->_mapFields( $values );

		$tmp	=	array();
		foreach( $values as $key => $value )
		{
			array_push( $tmp, $key.'='.$this->_db->quote( $value ) );
		}

		$query	=	'INSERT INTO '.$this->_table.' SET '.implode( ', ', $tmp );
		
		$this->_db->query( $query );
		return true;		
	}

   /**
	* updates an entry in the storage
	*
	* Implement this in the concrete storage container.
	*
	* @abstract
	* @param	object patForms		patForms object that should be stored
	* @return	boolean				true on success
	*/
	function _updateEntry( &$form, $primary )
	{
		$values = $form->getValues();
		
		$this->_prepareConnection();
		$values	=	$this->_mapFields( $values );
		$primary	=	$this->_mapFields( $primary );

		
		$tmp	=	array();
		foreach( $values as $key => $value )
		{
			array_push( $tmp, $key.'='.$this->_db->quote( $value ) );
		}

		$ptmp	=	array();
		foreach( $primary as $key => $value )
		{
			array_push( $ptmp, $key.'='.$this->_db->quote( $value ) );
		}

		$query	=	'UPDATE '.$this->_table.' SET '.implode( ', ', $tmp ).' WHERE '.implode( ' AND ', $ptmp );
		$this->_db->query( $query );
		return true;		
	}

   /**
	* check, whether an entry exists
	*
	* @access	private
	* @param	array
	*/
	function _entryExists( $primary )
	{
		$this->_prepareConnection();
		$primary	=	$this->_mapFields( $primary );

		$tmp	=	array();
		foreach( $primary as $key => $value )
		{
			array_push( $tmp, $key.'='.$this->_db->quote( $value ) );
		}

		$query	=	'SELECT * FROM '.$this->_table.' WHERE '.implode( ' AND ', $tmp );
		$result	=	$this->_db->getRow( $query, array(), DB_FETCHMODE_ASSOC );

		if( empty( $result ) )
			return false;

		return $result;
	}

   /**
	* map the values to the correct fields
	*
	* @access	private
	* @param	array		values
	* @return	array		values mapped to the correct fields
	*/
	function _mapFields( $values )
	{
		if( empty( $this->_fieldMap ) )
			return $values;

		$fields	=	array();
		foreach( $this->_fieldMap as $el => $field )
		{
			if( !isset( $values[$el] ) )
				continue;

			$fields[$field]	=	$values[$el];
		}
		return $fields;	
	}

   /**
	* map the fields to the correct elements
	*
	* @access	private
	* @param	array		values
	* @return	array		values mapped to the correct fields
	*/
	function _unmapFields( $values )
	{
		if( empty( $this->_fieldMap ) )
			return $values;

		$fields	=	array();
		foreach( $this->_fieldMap as $el => $field )
		{
			if( !isset( $values[$field] ) )
				continue;

			$fields[$el]	=	$values[$field];
		}
		return $fields;	
	}

   /**
	* prepare the DB connection
	*
	* @access	private
	*/
	function _prepareConnection()
	{
		if( $this->_db != null )
			return true;
		
		$this->_db	=	&DB::connect( $this->_dsn );
		return true;
	}
}
?>