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


class bx_plugins_gallery_overview extends bx_plugins_gallery {


    static private $instance = array();
    private $dirCreatetimeMap = array();
    private $DirReplacer = '';
    private $galleryPath = '/';
    private $pictureMode = 'random';
    private $virtualPath = '';

    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_gallery_overview($mode);
        }
        return self::$instance[$mode];
    }

    public function isRealResource($path , $id) {
        return true;
    }

    protected function __construct($mode) {

    }

    public function getContentById($path,$id) {

        $this->galleryPath = $path;

        $currentDir = $this->getVirtualGalleryRoot($path);
        $this->virtualPath =  $currentDir;
        $currentDir = BX_PROJECT_DIR.$currentDir;
        $this->DirReplacer = $currentDir;
        $currentDir = substr($currentDir,0,-1);
        $this->getAlbums($currentDir);

        //order albums by time, newest first
        krsort($this->dirCreatetimeMap);

        //create xml string
        $xml = '';
        foreach($this->dirCreatetimeMap as $album) {
            $xml .= "<album>".$this->arrayToXMLstring($album)."</album>";
        }
        $xml = "<overview>$xml</overview>";

        //and send back as dom
        return @domdocument::loadXML($xml);
    }

    private function arrayToXMLstring($array) {
        $xml = '';
        foreach($array as $key => $val) {
            if(is_array($val)) {
                $val = $this->arrayToXMLstring($val);
            }

            $id = '';
            if(is_numeric($key)) {
                $id = ' id="'.$key.'" ';
                $key = 'item';
            }
            $xml .= "<$key$id>$val</$key>";
        }
        return $xml;
    }


    /**
     *
     */
    private function getAlbums($dir) {
        $handle = opendir ($dir);
        while (false !== ($file = readdir ($handle))){
            if ((is_dir($dir."/".$file)) AND ($file != "."AND $file != ".." AND $file != '.svn')){

                $info = $this->getPictureInformation($dir."/".$file);
                //skip if no pictures are found
                if($info === false) {
                    continue;
                }

                $time = $info['latestPictureDate'];

                //prevent overwriting existing album
                while(isset($this->dirCreatetimeMap[$time])) {
                    $time++;
                }

                //create full picture-path to the picture (makex xsl easier ;))
                $info['showPicture'] = $info['picturePath'].$info[$this->pictureMode.'Picture'];
                $this->dirCreatetimeMap[$time] = $info;
                $this->getAlbums($dir."/".$file);
            }
        }
        closedir($handle);
    }

    private function getPictureInformation($dirName) {

        $dir = new ImageDirectoryIterator($dirName);
        $pictureCount = 0;

        foreach ($dir as $file) {
            $name = $file->getFileName();

            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            if(!$file->isDot() && $file->isReadable() && substr($name,0,1) !== ".") {
                if ($file->isImage()) {
                    $pictureCount++;

                    $MTime = $file->getMTime();

                    while(isset($_images[$MTime])) {
                        $MTime++;
                    }

                    $_images[$MTime] = $name;
                }
            }
        }




        if($pictureCount == 0) {
            return false;
        }

        $info = array();

        $info['link']  = str_replace($this->DirReplacer,'',$dirName)."/";
        $info['picturePath']  = $this->virtualPath.$info['link'];

        $coll = bx_collections::getCollection($this->galleryPath.$info['link']);


        $info['displayOrder'] = $coll->getDisplayOrder();
        if($info['displayOrder'] == 0) {
            //return false;
        }

        $info['displayName'] = $coll->getDisplayName();



        $info['pictureCount'] = $pictureCount;
        $info['pictures'] = $_images;

        krsort($_images);
        foreach($_images as $date => $file) {
            $info['latestPicture'] = $file;
            $info['latestPictureDate'] =  $date;
            break;
        }

        $rand_key = array_rand($_images);
        $info['randomPicture'] = $_images[$rand_key];

        $info['Directory'] = $dirName;

        return $info;
    }




}
