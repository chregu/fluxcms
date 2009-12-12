<?php

class bx_helpers_image {
    static private $getImgSizeData = NULL;
    
    static function getImgSize($src) {
        if (!self::$getImgSizeData) {
            $sc = popoon_helpers_simplecache::getInstance();
            
            self::$getImgSizeData = $sc->simpleCacheCheck("getImgSize","bx_helpers_image",null,"serialize",3600);
            
            if (!self::$getImgSizeData) {
                self::$getImgSizeData = array();
            }
            
        }   
        if (!isset(self::$getImgSizeData[$src])) {
            $filesrc = BX_PROJECT_DIR.str_replace("themes/","themes/".$GLOBALS['POOL']->config->theme."/",$src);
            $imgSize = @getimagesize($filesrc);
            self::$getImgSizeData[$src] = $imgSize;
            if (!isset($sc)) {
                $sc = popoon_helpers_simplecache::getInstance();
            }
            $sc->simpleCacheWrite("getImgSize","bx_helpers_image",null,self::$getImgSizeData);
        }
        return self::$getImgSizeData[$src];
    }
    
    static function getWidth($src) {
        list($width) = self::getImgSize($src);
        return $width;
    }

    static function getHeight($src) {
        list($width, $height) = self::getImgSize($src);
        return $height;
    }
    
    static function loadCropInterface($image) {
        ob_start();
       $ci = new Image_CropInterface();
        
       $ci->loadInterface($image );
        $ci->loadJavaScript($image);
       $c = ob_get_clean();
       $dom = new DomDocument();
       $dom->loadHTML('<html>'.$c.'</html>');
       return $dom;
       
    }
    public static function replaceDynimage($src,$size) {
       return preg_replace("#dynimages/[0-9]+/#","dynimages/$size/",$src);
   }

    public static function isImage($file) {
        $mimeType = popoon_helpers_mimetypes::getFromFileLocation($file);
        
        if (strstr($mimeType, "image") !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getFaviconAsBase64() {
        
        if (file_exists(BX_OPEN_BASEDIR.'files/favicon.ico')) {
            return base64_encode(file_get_contents(BX_OPEN_BASEDIR.'files/favicon.ico'));
        } else {
            return base64_encode(file_get_contents(BX_PROJECT_DIR.'favicon.ico'));
        }
    }
        
}
