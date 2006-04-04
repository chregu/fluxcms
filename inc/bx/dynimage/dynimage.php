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
    
    protected $config = NULL;
    protected $driver = array();
    protected $validator = array();
    protected $filters = array();
    
    public function __construct($config) {
        $this->config = $config;
        $this->driver = $config->getDriver();
        $this->validator = $config->getValidator();
        $this->filters = $config->getFilters();
    }
    
    public function printImage() {
        $oFilename = BX_PROJECT_DIR.bx_dynimage_request::getOriginalFilenameByRequest($this->config->request);
        if(!is_readable($oFilename))
            return FALSE;
        
        // set the current working image to be nothing
        $currentImage = FALSE;
        
        // get the size of the resulting image
        $imgOriginalSize = array();
        $imgSize = getimagesize($oFilename);
        $imgOriginalSize['w'] = $imgSize[0];
        $imgOriginalSize['h'] = $imgSize[1];
        $imgEndSize = $this->filters[0]->getEndSize($imgOriginalSize);
        
        // the last filter in the pipeline which modifys proportions
        // defines the size of the resulting image
        foreach($this->filters as $filter) {
            if($filter->modifysImageProportions())
                $imgEndSize = $filter->getEndSize($imgOriginalSize);
        }
        
        $currentImage = $this->driver->getImageByFilename($oFilename, $imgSize[2]);
        
        foreach($this->filters as $filter) {
            if($filter->getFormat() == $this->driver->getFormat()) {
                $filter->imageOriginalSize = $imgOriginalSize;
                $filter->imageEndSize = $imgEndSize;
                $currentImage = $filter->start($currentImage, $imgEndSize);
            }
        }
        
        header("Content-type: image/jpeg");
        print imagejpeg($currentImage);
        
        //d();
        
    }
     
    protected function printImageByFile($fname) {
    }
    
}
