<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+


class bx_plugins_xmlfile extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    
        /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_xmlfile($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        //make it possible to use existingDir/NonExistingDir/resource
        // NonExistingDir can then be used for some mode stuff like "print" 
        // maybe should be moved one step higher to the action itself
        $pos = strpos($name,"/");
        if ($pos !== false) {
            $name = substr($name,$pos+1);
        }
        $lang = $GLOBALS['POOL']->config->getOutputLanguage();
        $name = "$name.$lang";
        
        if (file_exists(BX_DATA_DIR.$path.$name.".xml")) {
            return "$name.xml";   
        }
    }
        
    
     public function getContentById($path, $id) {
       
        $dom = new domDocument();
        if (!$dom->load(BX_DATA_DIR.$path.$id)) {
            $src = BX_DATA_DIR.$path.$id;
             if (!file_exists($src) ) {
                throw new PopoonFileNotFoundException($src);
            } else if (!is_file($src)) {
                throw new PopoonIsNotFileException($src);
            } else {
                throw new PopoonXMLParseErrorException($src);  
            }
        }
        return $dom;
      
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    
}