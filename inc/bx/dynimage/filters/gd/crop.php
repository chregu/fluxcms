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
class bx_dynimage_filters_gd_crop extends bx_dynimage_filters_gd {
    
    public function start($imgIn) {
        $imgOut = imagecreatetruecolor($this->imageEndSize['w'], $this->imageEndSize['h']);
        
        $srcWidth = (int) round($this->imageOriginalSize['h'] * $this->imageEndSize['w'] / $this->imageEndSize['h']);
        
        imagecopyresampled($imgOut, $imgIn, 0, 0, 0, 0, $this->imageEndSize['w'], $this->imageEndSize['h'], $srcWidth, $this->imageOriginalSize['h']);
        imagedestroy($imgIn);
        return $imgOut;
    }
    
    public function getEndSize($imgSize) {
        $endSize = $imgSize;
        
        if(!empty($this->parameters['w']) && !empty($this->parameters['h'])) {
            $endSize['w'] = $this->parameters['w'];
            $endSize['h'] = $this->parameters['h'];
        }
        
        return $endSize;
    }
    
}
