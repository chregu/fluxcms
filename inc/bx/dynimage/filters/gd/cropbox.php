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
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category
 * @author Liip AG      <contact@liip.ch>
 */

/*
usage example:

<pipeline name="home">
    <filter type="cropbox">
        <parameter name="w" value="162" />
        <parameter name="h" value="100" />
    </filter>
</pipeline>

 */
class bx_dynimage_filters_gd_cropbox extends bx_dynimage_filters_gd {

    public function start($imgIn) {

        $imgOut = imagecreatetruecolor($this->imageEndSize['w'], $this->imageEndSize['h']);

        $ratioSrc = $this->imageOriginalSize['h']/$this->imageOriginalSize['w'];
        $ratioDest = $this->imageEndSize['h']/$this->imageEndSize['w'];

        if($ratioDest >= $ratioSrc) {
            // hÃ¶he bleibt
            $srcHeight = $this->imageOriginalSize['h'];
            $diffHeight = 0;
            // breite schneiden
            $ratio =  $this->imageEndSize['h']/$this->imageOriginalSize['h'];
            $srcWidth = $this->imageEndSize['w']/$ratio;
            $diffWidth = (int) round( abs($this->imageOriginalSize['w'] - $srcWidth) /2 );
        }
        else {
            // breite bleibt
            $srcWidth = $this->imageOriginalSize['w'];
            $diffWidth = 0;
            // hÃ¶he schneiden
            $ratio =  $this->imageEndSize['w']/$this->imageOriginalSize['w'];
            $srcHeight = $this->imageEndSize['h']/$ratio;
            $diffHeight = (int) round( abs($this->imageOriginalSize['h'] - $srcHeight) /2 );
        }

        $oh = $this->imageOriginalSize['h'];
        $ow = $this->imageOriginalSize['w'];
        $w = $this->imageEndSize['w'];
        $h = $this->imageEndSize['h'];
        imagecopyresampled($imgOut, $imgIn, 0, 0, $diffWidth, $diffHeight, $w, $h, $srcWidth, $srcHeight);
        imagedestroy($imgIn);
        return $imgOut;
    }

    public function getEndSize($imgSize) {
        $endSize = $imgSize;
        if(!empty($this->parameters['w']) && !empty($this->parameters['h'])) {
            $endSize['w'] = $this->parameters['w'];
            $endSize['h'] = $this->parameters['h'];
        }
        else {
            $endSize['w'] = 100;
            $endSize['h'] = 100;
        }
        return $endSize;
    }

}