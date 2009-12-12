<?php

class bx_collections {
    
    private static $collections = array();
    private static $collectionPaths = array();
    private function __construct($url) {
    }
    
    /**
    * gets an instance of a collection
    *
    * $url is the full URL (like /about/me.html)
    * this should return the collection from /about/
    *
    * if you want /about/me/i.html, but /about/me/ doesn't exist,
    *  it returns an exception, maybe we'll add later recursive support
    *  not sure if it's a very smart idea (could be useful for plugins, which
    *  build their own hierarchy)
    *
    * @param string $url
    * @return bx_collection 
    */
    
    public static function getCollection($url, $mode = 'output', $new = FALSE) {
        $dir = bx_collections::getCollectionUri($url);
        $id = "$mode:$dir";
        if (!isset(self::$collections[$id])) {
            bx_global::registerStream("bxconfig");
            
            if (!file_exists("bxconfig://".$dir)) {
                if ($new) {
                     self::$collections[$id] = new bx_collection($dir, $mode, true);
                } else {
                    return false;
                }
            }
            self::$collections[$id] = new bx_collection($dir, $mode);
        }
        return self::$collections[$id];
    }
    

    public static function getCollectionAndFileParts($url, $mode = "output") {
        $p = array();
        $p['coll'] = bx_collections::getCollection($url, $mode);
        $p['rawname'] = preg_replace("#^".$p['coll']->uri."#","",$url);
        $qpos = strpos($p['rawname'],"?");
        if ($qpos !== false) {
                 $p['rawname'] = substr($p['rawname'],0,$qpos );
        }
        $p = array_merge($p, bx_collections::getFileParts($p['rawname']));
        return $p;
    }
    
  
    public static function getCollectionUriAndFileParts($url) {
        $p = array();
        $p['colluri'] = bx_collections::getCollectionUri($url);
        $p['rawname'] = preg_replace("#^".$p['colluri']."#","",$url);
        $qpos = strpos($p['rawname'],"?");
        if ($qpos !== false) {
                 $p['rawname'] = substr($p['rawname'],0,$qpos );
        }
        $p = array_merge($p, bx_collections::getFileParts($p['rawname']));
        return $p;
    }
    
    public static function getFileParts($uri) {
        $p = array();
        $qpos = strpos($uri,"?");
        if ($qpos !== false) {
                 $uri = substr($uri,0,$qpos );
        }

        $dotPos = strrpos($uri,".");
        if($dotPos !== FALSE) {
            if ($dotPos + 1 == strlen($uri)) {
                $p['name'] = $uri;
                $p['ext'] = '';
            } else {
                $p['name'] = substr($uri, 0, $dotPos  );
                $p['ext'] = substr($uri, $dotPos + 1) ;
            }
        } else {
             
            $p['name'] = $uri;
            $p['ext'] = '';
        }
         
        if (preg_match('#^(.+)_([0-9:]+)$#',$p['name'],$matches)) {
            $p['name']= $matches[1];
            $p['number'] = str_replace(':',',',$matches[2]);
        } else {
            $p['number'] = "";
        }
        
        return $p;
    }
    
    public static function getCollectionUri($path, $fullpath = false) {
        if ( !isset(self::$collectionPaths[$path])) {
        $configDir = $path;
        if(strpos($path,'/admin/') === 0) {
            $dataDir = BX_ADMIN_DATA_DIR;
        } else {
            $dataDir = BX_DATA_DIR;
        }
        
        $configFile = $dataDir. $path . BX_CONFIGXML_FILENAME;
        
        if(!file_exists($configFile)) {
            $configDirChild = null;
            $dirs = explode('/', $path);
            $i = 0;
            
            for($i = 1; $i < sizeof($dirs); $i++) {
                $configDir = implode('/', array_slice($dirs, 0, sizeof($dirs) - $i));
                
                $configFile = $dataDir . $configDir.   '/'.BX_CONFIGXML_FILENAME;
                if((stripos($configFile, $dataDir) !== FALSE)) {
                    if (!$configDirChild && file_exists($configFile )) { 
                        break;
                    } else if ($configDirChild && file_exists($configFile.'.children' )) {
                        $configFile = $configFile.'.children';
                        $configDir = $configDirChild;
                        break;
                    }
                }
                    
                if (!$configDirChild && file_exists($dataDir . $configDir)) {
                    $configDirChild = $configDir;
                }
            }
        }
        if (substr($configDir,-1) != "/") {
            $configDir .= '/';
        }
        
        //$fullpath == 2 returns config file path instead of collection URI
        // needed by the bxconfig: stream
         self::$collectionPaths[$path] = array($configDir,$configFile);
        }
        if ($fullpath == 2) {
            return self::$collectionPaths[$path][1];
        } else if($fullpath) {
            return $dataDir.'/'.self::$collectionPaths[$path][0];
        } else {
            return self::$collectionPaths[$path][0];
        }
        
    }
    
    /**
    * cleans up a collection uri
    *
    * a collection uri has always to be the following format
    * /foo/bar/
    * foo/bar/ is invalid
    * /foo/bar as well
    * /foo//bar too
    */
    
    public static function sanitizeUrl($url) {
    	if (BX_OS_WIN) {
    		$url = str_replace('\\','/',$url);
    	}
        $url = '/'.preg_replace("#/{2,}#","/",trim($url,"/")).'/';
        if ($url == '//' || $url == '/./') {
            return '/';
        }
        return $url;
        
    }

    public static function getLanguage($fulluri) {
        
        $dirs = explode("/", $fulluri);
        if ($dirs[0] == "") {
            array_shift($dirs);
        }
        
        if (isset($dirs[0])) {
            if (preg_match("/^([a-z]{2})$/", $dirs[0], $match)) {
                // strip language from fulluri
                if(in_array($match[1], $GLOBALS['POOL']->config->outputLanguages)) {
                    array_shift($dirs);
                    $fulluri =  "/".implode("/", $dirs);
                
                    // reappend extension if it wasn't empty
                    //    $fulluri .= !empty($parts['ext']) ?  ".$parts[ext]" : '';
                    $_SESSION['lang'] = $match[1];
                    return array($fulluri, $match[1]);
                }
            } 
        }
        // no specific language has been requested. If browser language 
        // detection is on and no language is set in the session, get the
        // browser language.
        if($GLOBALS['POOL']->config->getConfProperty('detectBrowserLanguage') == 'true' && !isset($_SESSION['lang'])) {
            $browserLang = popoon_helpers_lang::preferredBrowserLanguage($GLOBALS['POOL']->config->outputLanguages);
            $_SESSION['lang'] = $browserLang;
        }

        return array($fulluri, BX_DEFAULT_LANGUAGE);
    }
        
}

?>
