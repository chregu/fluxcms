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
class bx_dynimage_filters_gd_resize extends bx_dynimage_filters_gd {
    
    protected $knownParameters = array('w', 'h');
    
    public function start($imgIn) {
        $imgOut = imagecreatetruecolor($this->imageEndSize['w'], $this->imageEndSize['h']);
        imagecopyresampled($imgOut, $imgIn, 0, 0, 0, 0, $this->imageEndSize['w'], $this->imageEndSize['h'], $this->imageOriginalSize['w'], $this->imageOriginalSize['h']);
        imagedestroy($imgIn);
        return $imgOut;
    }
    
    public function getEndSize($imgSize) {
        $endSize = array();
        $endSize['w'] = $imgSize['w'];
        $endSize['h'] = $imgSize['h'];
        
        // 'w' has precedence over 'h', so w:nn,h:mm will create a picture of nn pixels width 
        if(!empty($this->parameters['w'])) {
            if($this->parameters['w'] < $imgSize['w']) {
                $endSize['w'] = $this->parameters['w'];
                $endSize['h'] = (int) round($this->parameters['w'] * $imgSize['h'] / $imgSize['w']);
            }
        }
        if(!empty($this->parameters['h']) && empty($this->parameters['w'])) {
            if($this->parameters['h'] < $imgSize['h']) {
                $endSize['h'] = $this->parameters['h'];
                $endSize['w'] = (int) round($imgSize['w'] / $imgSize['h'] * $this->parameters['h']);
            }
        }
        
        return $endSize;
    }
    
}
