<?php

function __autoload($class) {
    $prefix = substr($class,0,3);
    switch($prefix) {
        
        // bx interface
        case "bxI":
            $incFile = BX_LIBS_DIR.DIRECTORY_SEPARATOR.'interfaces'.DIRECTORY_SEPARATOR.substr($class,3).'.php';
        break;

        // popoon class
        case "pop":
            if ($class == 'popoon') {
                $incFile = BX_POPOON_DIR.DIRECTORY_SEPARATOR.'popoon.php';
            } else if(!strncmp($class, 'popoonI', 7)) {
                $incFile = BX_POPOON_DIR."interfaces".DIRECTORY_SEPARATOR.substr($class,7).'.php';
            } else {
                // files in bx_libs/popoon can overwrite opoon includes
                $incFile = BX_LIBS_DIR.DIRECTORY_SEPARATOR.'popoon'.DIRECTORY_SEPARATOR.str_replace("_",DIRECTORY_SEPARATOR,substr($class,6)).'.php';
                if(!file_exists($incFile)) {
                    $incFile = BX_POPOON_DIR.str_replace("_",DIRECTORY_SEPARATOR,substr($class,6)).'.php';
                }
                    
            }
        break;

        // regular bx class
        case "bx_":
            $incFile = BX_LIBS_DIR.str_replace("_",DIRECTORY_SEPARATOR,substr($class,3)).".php";
        break;

        // everything else
        default:
            if (substr($class, -9) == "Exception") {
                if (!strncmp($class,"Popoon",6)) {
                    $incFile = BX_POPOON_DIR.'exceptions/'.substr(substr($class,6),0,-9).'.php';    
                } else if (!strncmp($class,"Bx",2)) {
                    $incFile = BX_LIBS_DIR.'exceptions/'.substr(substr($class,2),0,-9).'.php';    
                } else {
                    $incFile = BX_LIBS_DIR.'exceptions/'.$class.'.php';
                }
            } else {
                $incFile = str_replace("_",DIRECTORY_SEPARATOR,$class).".php";
            }
    }

    if (isset($GLOBALS['POOL']) && $GLOBALS['POOL']->debug) {
        include_once(BX_LIBS_DIR.'helpers/debug.php');
        bx_helpers_debug::$incFiles[] = $incFile;
    }

    /* 
     * Never ever allow any files being included, which could be remote locations.
     *  The possibility that $incFile is some URL to a remote location (like http://bad.org/evil.php)
     *  is pretty nil here. Furthermore we turn off allow_url_fopen in .htaccess.
     *  But nevertheless we check here again, just to be sure.. 
     */
     
    if (strpos($incFile,"://") >= 2 && substr($incFile,0,5) !="file:") {
        print("autoloader didn't load $class from $incFile (no URLs allowed)\n");
        
        // first check if file exists, then try to include it, only works for absolute urls
        // and not stuff from PEAR for example
        // that's the usual way,....
    } else if (file_exists($incFile)) {
        include($incFile);
    } else {
        // if there's no / at the beginning, it's most certainly something in the include path...
        // try to load it from there (file_exists won't work)
        // as this also might not work, (if in BX_LOCAL_INCLUDE_DIR), we have to shut
        // off error warnigns with @. THIS WAY NO ERRORS ARE SHOWN, NOT EVEN PARSE ERRORS...  
        if (substr($incFile,0,1) != '/' || (BX_OS_WIN && preg_match("#^[a-zA-Z]:#",$incFile))) {
            if (@include($incFile)) {
                return;
            }
        }
        
        //if still not included, try alternate location for  classes
        // we assume, that this is always a full path..
        $incFile = BX_LOCAL_INCLUDE_DIR.str_replace("_",DIRECTORY_SEPARATOR,$class).'.php';
        if(file_exists($incFile) && include($incFile)) {
            return;
        }
        
        //the unit tester tries to load some methods, do suppress the error here
        if(strpos($incFile,"test") !== 0) {
            error_log("autoloader couldn't load $class from $incFile\n");
        }
    }
}

?>
