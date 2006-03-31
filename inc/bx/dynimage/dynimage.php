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
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dynimage_dynimage {
    
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    protected $request = '';
    
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    protected $method = '';
    
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    protected $pipeline = '';
    
    public $filters = array();
    
    /**
     *  constructor
     *
     *  @access public
     */
    public function __construct() {
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function printImage() {
        var_dump($this);
    }
     
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function printImageByFile($fname) {
    }
    
}
