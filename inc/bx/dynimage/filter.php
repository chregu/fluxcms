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
class bx_dynimage_filter {
    
    protected $knownParameters = array();
    protected $parameters = array();
    
    public function modifysImageProportions() {
        return FALSE;
    }
    
    public function getEndSize($imgSize) {
        return FALSE;
    }
    
    public function setParameters($params) {
        foreach($params as $p => $v) {
            if(in_array($p, $this->knownParameters))
                $this->parameters[$p] = $v;
        }
    }
    
}

