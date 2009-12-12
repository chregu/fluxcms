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
    protected $outputContentType;
    protected $additionalFileTypes = array('tif','tiff','pdf','psd', 'ps');
    
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
        $this->outputContentType = popoon_helpers_mimetypes::getFromFileLocation($this->cacheFilename);

        if(!$this->cacheFileIsValid() || !$this->doCache) {
            if(!is_readable($this->originalFilename))
                $this->send404andDie();
            
            $this->parseConfig();
            
            // set the current working image to be nothing
            $currentImage = FALSE;
            
            // try to get the size of the original image
            $imgSize = getimagesize($this->originalFilename);
            
            if($imgSize === FALSE || ($imgSize!==FALSE && !$this->driver->isImgTypeSupported($imgSize[2]))) {
                
                // check if we can convert the file using imagemagick
                if(($convertedImageFileName = $this->preprocessImage())!==FALSE) {
                    // overwrite original file name and reread original image size, type and output content type
                    $imgSize = getimagesize($convertedImageFileName);
                    $this->originalFilename = $convertedImageFileName;
                    $this->outputContentType = 'image/jpeg';
                    
                    // preprocess converts to jpg
                    $this->originalImageType = IMAGETYPE_JPEG;
                    
                    // create the working image;
                    $currentImage = $this->driver->getImageByFilename($this->originalFilename, $this->originalImageType);
                    
                    // delete the temp file
                    unlink($convertedImageFileName);
                    
                } else {
                    die('unsupported file type');
                }
            } else {
                // set original image type
                $this->originalImageType = $imgSize[2];
                $currentImage = $this->driver->getImageByFilename($this->originalFilename, $this->originalImageType);
            }
            
            $imgOriginalSize = array();
            $imgOriginalSize['w'] = $imgSize[0];
            $imgOriginalSize['h'] = $imgSize[1];
            
            // get the size of the resulting image
            $imgEndSize = $this->filters[0]->getEndSize($imgOriginalSize);
            
            
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

        header('Content-type: '.$this->outputContentType);
        
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
        $cfName = 'dynimages/'.$p['parameterstring'].$p['filename'];
        $info = pathinfo($cfName);
        if(in_array($info['extension'], $this->additionalFileTypes)) {
            $cfName.='.jpg';
        }
        return $cfName; 
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
    
    protected function preprocessImage() {
     
        $info = pathinfo($this->originalFilename);
        
        if(in_array($info['extension'], $this->additionalFileTypes)) {
            $tmpfile = tempnam(BX_TEMP_DIR, 'dynimg_');
            
            $origFile = $this->originalFilename;
            
            if($info['extension']=='pdf' || $info['extension']=='ps') {
                $origFile.= '[0]';
            }
            
            $command = 'convert';
            $command.= " ".escapeshellarg($origFile);
            $command.= " jpg:".escapeshellarg($tmpfile);
            
            exec($command, $output, $exitcode);
            if($exitcode===0) {
                return $tmpfile;
            } else {
                return FALSE;
            }
            
        } else {
            return FALSE;
        }
        
        
    }
    
    
}
