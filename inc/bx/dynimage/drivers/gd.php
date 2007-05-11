<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
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
class bx_dynimage_drivers_gd {
    
    public $name = 'gd';

    public function getFormat() {
        return 'gd';
    }
    
    public function getImageByFilename($file, $imgType) {
        switch($imgType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($file);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($file);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($file);
        }
        return FALSE;
    }
    
    public function saveImage($image, $filename, $imgType) {
        switch($imgType) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $filename);
            case IMAGETYPE_PNG:
                return imagepng($image, $filename);
            case IMAGETYPE_GIF:
                return imagegif($image, $filename);
        }
        return FALSE;
    }
    
}

