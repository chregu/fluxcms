<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
//
// $Id$


/**

* @author   Christian Stocker <chregu@liip.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_readers_webdavadmin extends popoon_components_reader {


    /**
    * Constructor, does nothing at the moment
    */
    function __construct ($sitemap) {
        parent::__construct($sitemap);
    }

   
    function init($attribs)
    {
        parent::init($attribs);
    }    
    
    function start()
    {
        $dirs = array("themes","xml","files","structure","forms");
        
        
       
        $pos = strpos($_SERVER['REQUEST_URI'],"#") || strpos($_SERVER['REQUEST_URI'],"%23");
       
        if ($pos) {
            $this->sitemap->uri .= substr($_SERVER['REQUEST_URI'], $pos,strlen($_SERVER['REQUEST_URI']));
        }
        $webroot = preg_replace("#^/*#","",$this->getParameterDefault("webroot"));
        
        // strip webroot from uri and add it to PATH_INFO
        // this is the place where WebDAV_Server looks for the files in the filesystem
        
        $_SERVER["PATH_INFO"] = str_replace($webroot,"",$this->sitemap->uri);
        // add it to scriptname, otherwise the return in ls are not correct
        // the else is a special case, if it's the root file
        
        $webrootPrepend = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"$webroot"));
        if ($webroot || $_SERVER["PATH_INFO"]) {
            $_SERVER["SCRIPT_NAME"] = str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]).$webrootPrepend.$webroot;
        } else {
            $_SERVER["SCRIPT_NAME"] = str_replace("/index.php","",$_SERVER["SCRIPT_NAME"]);
        }
        
        include_once("bx/popoon/components/readers/webdav/admin.php");
         
        $w = new HTTP_WebDAV_Server_admin();
        $w->db = $GLOBALS['POOL']->db;
        $w->ServeRequest($this->getParameterDefault("fsroot"));
        
        
        
       
        return True;
    }
  
    /* CACHING STUFF */

    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component'
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    /** Generate validityObject  
     *
     * This is common to all "readers", you'll find the same code there.
     * I'm thinking about making a method in the class component named generateValidityFile() or alike
     * instead of having the same code everywhere..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @see  checkvalidity()
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    function generateValidity(){
        $validityObject = $this->attribs;
        $src = $this->getAttrib("src");
        $validityObject['filemtime'] = filemtime($src);
        $validityObject['fileatime'] = fileatime($src);
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache
     *
     * This implements only the most simple form: If there's no fresher version, take that from cache.
     * I guess we'll need some more refined criteria..
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    function checkValidity($validityObject){
        return(isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] == filemtime($validityObject['src'])));
    }

}


?>
