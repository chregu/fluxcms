<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dynimage_filter {
    
    protected $knownParameters = array();
    protected $parameters = array();
    
    public function modifysImageProportions() {
        return FALSE;
    }
    
    public function getEndSize($imgSize) {
        return $imgSize;
    }
    
    public function setParameters($params) {
        $this->parameters = $params;
    }
    
}

