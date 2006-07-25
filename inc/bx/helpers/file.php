<?php

class bx_helpers_file {
    static private $getFileSizeData = null;
    
    static function getFileSize($src, $human = true) {
        if (!self::$getFileSizeData) {
            $sc = popoon_helpers_simplecache::getInstance();
            
            self::$getFileSizeData = $sc->simpleCacheCheck("getFileSize","bx_helpers_file",null,"serialize",3600);
            
            if (!self::$getFileSizeData) {
                self::$getFileSizeData = array();
            }
            
        }   
        
        if (!isset(self::$getFileSizeData[$src])) {
            if (strpos($src,'http://') === 0) {
                
                $c = new HTTP_Client();
                $c->head( $src);
                $cr = $c->currentResponse()  ;
                $size =  $cr['headers']['content-length'];
            } else {
                //$filesrc = BX_PROJECT_DIR.str_replace("themes/","themes/".$GLOBALS['POOL']->config->theme."/",$src);
                $filesrc = BX_OPEN_BASEDIR.$src;
                if (file_exists($filesrc)) { 
                    $size = filesize($filesrc);
                } else {
                    $size=0;
                }
            }
            
            if ($human) {
                $size = round($size/1024);
                if ($size > 1000) {
                    $size = round($size/1024,2) . " MB";
                } else {
                    $size .=" kB";
                }
            }
            self::$getFileSizeData[$src] = $size;
            if (!isset($sc)) {
                $sc = popoon_helpers_simplecache::getInstance();
            }
            $sc->simpleCacheWrite("getFileSize","bx_helpers_file",null,self::$getFileSizeData);
        }
        
        return self::$getFileSizeData[$src];
    }
    
    
    /** creates a full path...
    */
    static  function mkpath($path) {
        $path = preg_replace("#/+#","/",$path);
        //bx_helpers_debug::webdump(substr($path,0,1));
        $dirs = explode("/",$path);
        $path = $dirs[0];
        for($i = 1;$i < count($dirs);$i++) {
            $parent = $path;
            $path .= "/".$dirs[$i];
            if(is_readable($parent) && !is_dir($path)  ) {
                mkdir($path,0755);
            }
        }
    }
    
    /**
     *  Recursively deletes the given directory.
     *
     *  @param  string $dir Directory to delete 
     *  @access public
     */
    static function rmdir($dir, $deleteitself = true) {
        $all = glob($dir.'/*');
        $hidden = glob($dir.'/.*');
        $objs = array_merge($all, $hidden);        
        if(sizeof($objs) > 0) {
            foreach($objs as $obj) {
                if($obj != $dir.'/.' AND $obj != $dir.'/..') {
                    if(file_exists($obj) AND !is_writable($obj)) {
                        chmod($obj, 0666); 
                    }
                   
                    if (is_dir($obj)) {
                        bx_helpers_file::rmdir($obj);
                    } else {
                        unlink($obj);
                    }
                }
            }
        }
        if ($deleteitself) {
            rmdir($dir);
        }
    }
    
    static function cpdir($dir,$todir,$noSvn = true) {
        $folder = opendir($dir);
        if (!file_exists($todir)) {
            mkdir($todir,0755,true);
        }
        while($file = readdir($folder)){
           if ($file == '.' || $file == '..') {
               continue;
           }
           if ($noSvn && $file == ".svn") {
               continue;
           }
           
           if(is_dir($dir.'/'.$file)){
               self::cpdir($dir.'/'.$file,$todir.'/'.$file);
           } else {
               
               if (!copy($dir.'/'.$file,$todir.'/'.$file)) {
                   print 'Could not copy '.$dir.'/'.$file .' to ' . $todir.'/'.$file."<br/>";
               }
           }
        }
        closedir($folder);
    }
}
