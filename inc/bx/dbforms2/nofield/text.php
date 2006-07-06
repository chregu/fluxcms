<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id: xml.php 4863 2005-07-01 16:00:46Z philipp $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_nofield_text extends bx_dbforms2_nofield {
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'text';
    }
    
	/**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function setValue($value){
		$this->value = $this->getAttribute('value');
	}
	
	/**
	 * 
	 */
	 public function getSQLName(){
		return '';
	 }

}

?>
