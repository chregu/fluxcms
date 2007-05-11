<?php

// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Liip AG                                           |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Matthias Dieke <matthias.dieke@gmx.de>                       |
// +----------------------------------------------------------------------+

/**
 * class bx_plugins_gallery_preview
 * @package bx_plugins
 * @subpackage gallery
 * */
class bx_plugins_gallery_preview extends bx_plugins_gallery{
    static private $instance = array();
    protected $dirCreatetimeMap = array();
    
    public static function getInstance($mode) {

            if (!isset(self::$instance[$mode])) {
                self::$instance[$mode] = new bx_plugins_gallery_preview($mode);
            } 
            return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
    
    }
    

    
    public function getContentById($path,$id) {
    
        $this->dirCreatetimeMap = array();

        if ($pos = strpos($id,"(")) {
            $pos2 = strpos($id,")");
            $params = substr($id,$pos+1, $pos2 - $pos - 1);
            $id = substr($id,0,$pos);
            $params = explode(",",$params);
        }  else {
            //
            // this plugin need params
            // if user forget to set this use the following
            //
            $params = array(1,4,"latest", false);
        }
        
        //
        // for better readable source-code ...
        //
        $rowsPerPage     = $params[0];
        $columnsPerPage  = $params[1];
        $imgFetchMode    = $params[2];
        (empty($params[3])) ? $ignoreSubDir = false : ($params[3] == "true") ? $ignoreSubDir =  true: $ignoreSubDir = false;
        
        
        $galleryRoot = $this->getGalleryRoot($path);
        
        $currentDir = trim($path, "/");
        
        //
        // when $currentDir still has slashes like; gallery/sub_gallery...
        // ... get the string after the last "/" 
        //
        if (strrpos($currentDir, "/") !== false) {
            $currentDir = substr($currentDir, strrpos($currentDir, "/")+1);
        }

        
        $pathParentDir = str_replace("/".$currentDir."/","",$galleryRoot);  
        $pathParentDir = "/".$pathParentDir;
        
        
        //
        // store the MTime and the name of the parent-dir to 
        // $dirCreatetimeMap
        //
        
        $dirIterator = new DirectoryIterator($pathParentDir);
        
        foreach($dirIterator as $file) {   

            if ($file->isDir() && $file->getFilename() == $currentDir) {  
                $this->dirCreatetimeMap[$file->getMTime()] = $path; 
            }
        }


        if (!$ignoreSubDir) {
            $this->getSubgalleries($this->getGalleryRoot($path), $path);
        }
        
        
        $path = $this->getPathOfLatestAlbum($this->getTsOfLatestAlbum());
        
        $coll = bx_collections::getCollection($path);
        
        $galleryName = $coll->getDisplayName();
        

        $options = array();

        $dom = new domDocument();
        $drivers = array();
        
        if (count($drivers) == 0) {
        
            $drivers[] = bx_plugins_gallery_preview_file::getInstance($dom,$path,$id);
        }
        
        
        $virtualRoot = $this->getVirtualGalleryRoot($path);
        
	$relPath = '';
	
	
        $options['root'] = $this->getGalleryRoot($path).$relPath;

        
        $dom->appendChild($dom->createElement('gallery'));
        $options['path'] =  $this->getVirtualGalleryRoot($path).$relPath;
        $dom->documentElement->setAttribute('path', $options['path']);

        $dom->documentElement->setAttribute('mode', "preview");
        
        $dom->documentElement->setAttribute('collUri', $path);
        
        $dom->documentElement->setAttribute('name', $galleryName);
        
        
        $options['images'] = $dom->createElement('images');
        $options['albums'] = $dom->createElement('albums');

        $options['numberOfImages'] = 0;
        $options['numberOfAlbums'] = 0;

        // calculate images per page
        
        $options['rowsPerPage']     = $rowsPerPage;
        $options['columnsPerPage']  = $columnsPerPage;
        $options['imagesPerPage']   = $options['rowsPerPage'] * $options['columnsPerPage'];
        
       if ( $imgFetchMode == "random") {
              $options['imgFetchMode'] = "random";
          }elseif ($imgFetchMode == "first") {
              $options['imgFetchMode'] = "first";            
          }else{
              $options['imgFetchMode'] = "latest";
       }
      
        // get currentPage from request vars
        $options['currentPage'] = 1;
      
       foreach ($drivers as $d) {
             $d->getImagesAndAlbums($options);
        }

        
/*        if ($flickrParams) {
            $f = new Services_flickr($flickrParams['userid']);
            $sets = $f->getPhotoSets();
            foreach($sets as $set) {
                $node = $dom->createElement('album');
                $node->setAttribute('name', $set['title']);
                $node->setAttribute('href', $path.'flickr'.$set['id']."/");
                $options['albums']->appendChild($node);
                $options['numberOfAlbums']++;
            }
        }
*/        
        // create a new pager
        $pagerID = 'gallery';
        bx_helpers_pager::initPager($pagerID);

        // update pager and create pager node
        bx_helpers_pager::setNumberOfEntries($pagerID, $options['numberOfImages']);
        bx_helpers_pager::setEntriesPerPage($pagerID, $options['imagesPerPage']);
        bx_helpers_pager::setCurrentPage($pagerID, $options['currentPage']);
        $pagerNode = $dom ->createElement('pager');
        $pagerNode->setAttribute('numberOfEntries', $options['numberOfImages']);
        $pagerNode->setAttribute('numberOfPages', bx_helpers_pager::getNumberOfPages($pagerID));
        $pagerNode->setAttribute('nextPage', bx_helpers_pager::getNextPage($pagerID));
        $pagerNode->setAttribute('prevPage', bx_helpers_pager::getPrevPage($pagerID));
        $pagerNode->setAttribute('currentPage', $options['currentPage']);

        // create ParameterNode and sub-nodes
        $params = $dom->createElement("parameters");
        $parameter_1 = $dom->createElement("parameter");
        $parameter_1->setAttribute("name", "columnsPerPage");
        $parameter_1->setAttribute("value", $options['columnsPerPage']);
       
        $parameter_2 = $dom->createElement("parameter");
        $parameter_2->setAttribute("name", "rowsPerPage");
        $parameter_2->setAttribute("value", $options['rowsPerPage']);
       
        $params->appendChild($parameter_1);
        $params->appendChild($parameter_2);
        
        $albumTree = $dom->importNode($this->getAlbumTree($this->getGalleryRoot($path), $relPath), TRUE);

        $dom->documentElement->appendChild($options['images']);
        $dom->documentElement->appendChild($options['albums']);
        $dom->documentElement->appendChild($params);
        $dom->documentElement->appendChild($pagerNode);
        $dom->documentElement->appendChild($albumTree);

        return $dom;

    
    }
    
    
    protected function getSubgalleries($path, $coll) {
    
    
        if (is_dir($path)) {
            $dir  = new RecursiveDirectoryIterator($path);
        
            foreach($dir as $file) {
                if ($file->isDir()) {
                    $pos = strpos($file->getPathname(), $coll);
                
                    $pathname =  substr($file->getPathname(), $pos);
                
                    $this->dirCreatetimeMap[$file->getMTime()] = $pathname."/";
                    

                    $this->getSubgalleries($file->getPathname(), $coll);
                }
            }
       }
    }
    
    
    protected function getTsOfLatestAlbum() {
    
        $tmpTs = 0;
        
        foreach($this->dirCreatetimeMap as $ts => $path) {        
            if ($ts > $tmpTs) {
                $tmpTs = $ts;
            }        
        }        
        return $tmpTs;    
    }
    
    protected function getPathOfLatestAlbum($ts) {
        return $this->dirCreatetimeMap[$ts];
    }
}


class bx_plugins_gallery_preview_file {
    static function getInstance($dom,$path,$id) {
            return new bx_plugins_gallery_preview_file($dom,$path,$id);
    }
    
    function __construct ($dom,$path, $id) {
        $this->root = $id;
        $this->dom = $dom;
        
    }
    
    function getImagesAndAlbums(& $options) {

        $dir = new ImageDirectoryIterator($options['root']);
        
        $i = 0;
        $tmpMTime = 0;
        foreach ($dir as $file) {
            $name = $file->getFileName();
                
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            if(!$file->isDot() && $file->isReadable() && substr($name,0,1) !== ".") {
                if ($file->isDir()) {
                    $node = $this->dom->createElement('album');
                    $node->setAttribute('name', $name);
                    $node->setAttribute('href', $name.'/');
                    $options['albums']->appendChild($node);
                    $options['numberOfAlbums']++;
                } else if ($file->isImage()) {
                
                    //
                    // if a archive was uploaded the MTime of the pictures is the
                    // same...
                    // ... so we have to add something ;)
                    //
                
                    if ($file->getMTime() == $tmpMTime) {
                       $i++;
                       $MTime = $file->getMTime() +  $i; 
                     
                    }else {
                        $MTime = $file->getMTime();
                        $tmpMTime = $MTime;
                    }
                    $_images[$MTime] = $name;           
                    
                    
                    //
                    // counting the number of every image can later be used 
                    // as info in the xsl file
                    //
                    // e.g. "This is a preview. The hole gallery has xxx pictures.
                    //

                    $options['numberOfImages']++;
                }
            }
        }

        $ct = $options['imagesPerPage'];
        
        if ($options['imgFetchMode'] == "random") {
            for($i=0; $i<$ct; $i++) {
                shuffle($_images);               
                $name = array_shift($_images);
                $options['images']->appendChild($this->createImageNode($name));
            }
        }elseif ($options['imgFetchMode'] == "latest")  {
            $_images = array_flip($_images);
            array_multisort($_images, SORT_DESC);
            $_images = array_flip($_images);
           
        }
        
        if ($options['imgFetchMode'] == "latest" || $options['imgFetchMode'] == "first") {       
          
          $i = 0;
          foreach($_images as $ts => $name) {
              $options['images']->appendChild($this->createImageNode($name));
              $i++;
              if ($i == $ct) break;
          } 
     
        }
       return true;
    }
    
    protected function createImageNode($nodeName) {
       
        $node = $this->dom->createElement('image');
        $node->setAttribute('href', $nodeName);
        $node->setAttribute('id', $nodeName);
        
        return $node;
       
    
    }
    
}
