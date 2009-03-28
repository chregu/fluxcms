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
class bx_dynimage_dynimage {
    
    protected $config = NULL;
    protected $driver = array();
    protected $validator = array();
    protected $filters = array();
    protected $doCache = true;
    
    public function __construct($request) {
        $this->request = $request;
    }
    
    protected function parseConfig() {
        $this->config = new bx_dynimage_config($this->request);
        $this->driver = $this->config->getDriver();
        $this->validator = $this->config->getValidator();
        $this->filters = $this->config->getFilters();
    }
    
    public function printImage() {
        
        $this->originalFilename = BX_PROJECT_DIR.bx_dynimage_request::getOriginalFilenameByRequest($this->request);
        $this->cacheFilename = BX_PROJECT_DIR.$this->getCacheFilenameByRequest($this->request);

        if(!$this->cacheFileIsValid() || !$this->doCache) {
            if(!is_readable($this->originalFilename))
                $this->send404andDie();
            
            $this->parseConfig();
            
            // set the current working image to be nothing
            $currentImage = FALSE;
            
            // get the size of the resulting image
            $imgOriginalSize = array();
            $imgSize = getimagesize($this->originalFilename);
            $this->originalImageType = $imgSize[2];
            $imgOriginalSize['w'] = $imgSize[0];
            $imgOriginalSize['h'] = $imgSize[1];
            $imgEndSize = $this->filters[0]->getEndSize($imgOriginalSize);
            $currentImage = $this->driver->getImageByFilename($this->originalFilename, $this->originalImageType);
            
            foreach($this->filters as $filter) {
                if($filter->getFormat() == $this->driver->getFormat()) {
                    $filter->imageOriginalSize = $imgOriginalSize;
                    $filter->imageEndSize = $imgEndSize;
                    $currentImage = $filter->start($currentImage, $imgEndSize);
                }
            }
            $this->createCacheDir();        
            $this->driver->saveImage($currentImage, $this->cacheFilename, $this->originalImageType);
        }

        header('Content-type: '.popoon_helpers_mimetypes::getFromFileLocation($this->cacheFilename));
        
        if($this->doCache) {
            header('Last-Modified: '.date('r', $this->lastModified));
            $now = time();
            header('Expires: '. date('r', $now + ($now - $this->lastModified)));
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $lastMod304 = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                if ($lastMod304 >= $this->lastModified) {
                    header('Not Modified', TRUE, 304);
                    exit;
                }
            }
        }
        print file_get_contents($this->cacheFilename);
    }
     
    protected function getCacheFilenameByRequest($request) {
        $p = bx_dynimage_request::getPartsByRequest($request);
        return 'dynimages/'.$p['parameterstring'].$p['filename']; 
    }
    
    protected function cacheFileIsValid() {
        $this->lastModified = filemtime($this->originalFilename);
        if(file_exists($this->cacheFilename) && (filemtime($this->cacheFilename) >= $this->lastModified )) {
            return TRUE;
        } 
        return FALSE;
    }
    
    protected function createCacheDir() {
        $fi = pathinfo($this->cacheFilename);
        if (!file_exists($fi['dirname'])){
           if (!mkdir($fi['dirname'], 0777, TRUE)) {
               die($fi['dirname'] ." is not writable ");
           }
        }
    }
    
    protected function send404andDie() {
        header("Not Found", TRUE, 404);
        print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML><HEAD>
<TITLE>404 Not Found</TITLE>
</HEAD><BODY>
<H1>Not Found</H1>
The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.<P>

</BODY></HTML>';
        die();   
    }
    
    
}
