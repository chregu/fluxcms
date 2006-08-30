<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
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
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

/**
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/

class popoon_components_actions_bxcms extends popoon_components_action {
    
    /**
    * Constructor
    *
    */
    function __construct(&$sitemap) {
        parent::__construct($sitemap);
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function act() {
        // set X-PoweredBy
        if ($this->getParameterDefault("noPoweredBy") != "true") {
            $this->sitemap->setHeader("X-CMS-Powered-By", "Flux CMS " . BXCMS_VERSION.'/'.BXCMS_REVISION ." <http://flux-cms.org>");
        }
        

        // check for an id...
        $mode = "output";
        if (isset($_GET["admin"])) {
            $this->sitemap->options->popoonmap["admin"] = true;
        }

        
        if ($id = $this->getParameterDefault("id")) {
            $parts =  bx_collections::getCollectionAndFileParts($id, $mode);
            $fulluri = $parts['coll']->getRequestById($parts['rawname']);
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
        } else {
        
            $fulluri = "/".$this->getAttrib("uri");
            
            /* using _ as start of a (virtual)collection is not allowed for external requests
             * only for internal for example in bx_streams_blog 
             *   $xml = $p->getContentById("/","_all/index");
             */
            if (strpos($fulluri,"/_") > 0) {
                 throw new BxPageNotFoundException(substr($_SERVER['REQUEST_URI'],1));
            }
            $mo = false;
	    
            if ( ( $mo =strpos($fulluri,'/mo/') === 0) || (isset($_COOKIE['isMobile']) && $_COOKIE['isMobile'] == "true") ) {
                
                if (isset($_GET['isMobile']) && $_GET['isMobile'] == "false") {
                    setcookie("isMobile", false, 0, "/");
                } else {
                    setcookie("isMobile", "true", time()+60*60*24*30*6,"/");
                    $GLOBALS['POOL']->config->theme = "mobile";
                    $GLOBALS['POOL']->config->themeCss = "main.css";
                }
                if ($mo) {
                    $fulluri = substr($fulluri,3);
                }
            }
            if (strpos($fulluri, ".") === false) {
                //if no / at the end of fulluri and no . in filename, we assume, it's a subcollection
                // and do redirect here
                if (substr($fulluri,-1) != "/") {
                    header("Location: ".BX_WEBROOT.preg_replace("#^/#","",$fulluri)."/");
                    die();
                }
                $fulluri .= "index.html";   
            }
            
            list($fulluri, $lang) = bx_collections::getLanguage($fulluri);
            $GLOBALS['POOL']->config->setOutputLanguage($lang);

            //comma to GET parameter...
            if (($pos = strpos($fulluri,",")) !== false) {
                $_gets = str_replace(",","&",substr($fulluri,$pos + 1));
                parse_str(str_replace('$_$',"/",$_gets),$vars);
                foreach($vars as $key => $value) {
                    if (!isset($_REQUEST[$key])) {
                        
                        $_REQUEST[$key] = $value;
                        $_GET[$key] = $value;
                    }
                }
                $fulluri = substr($fulluri,0,$pos);
            }
           
            
        }
        
        $parts = bx_collections::getCollectionAndFileParts($fulluri, $mode);
        $collection = $parts['coll'] ;
        if (!$collection) {
            throw new Exception ("No collection object found");
        }
        
        if($GLOBALS['POOL']->config->advancedRedirect == 'true'){
            /*
            * userdir
            */
            $userdir = bx_resourcemanager::getFirstPropertyAndPath($fulluri,'redirect');
            if( $userdir !== NULL && $userdir['property'] == '{userdir}' ){
                
                $user = bx_helpers_perm::getUsername();
                if($user != ''){
                    $fulluri = str_replace($userdir['path'], '', $fulluri);
                    $fulluri = $userdir['path'].'/'.$user.$fulluri;
                    $parts = bx_collections::getCollectionAndFileParts($fulluri, $mode);
                    $collection = $parts['coll'];
                }
            } 
        }
        /* 	Check for redirect 
        * 	Old "normal" redirect ;)
        */    
	$redirect = $collection->getProperty('redirect');
        if ( $redirect !== NULL && ($parts['rawname'] == 'index.html') && $redirect != '{userdir}' ) {
            
            // absolute path
            if (strpos($redirect, '/') === 0) {
                $fulluri = $redirect;
            } else {
                if(strpos($redirect, '.') === FALSE) {
                    $fulluri = preg_replace("#[a-zA-Z_\-]+\.[a-z]+$#", sprintf("%s\\0", $redirect), $fulluri);
                } else {
                    $fulluri = preg_replace("#[a-zA-Z_\-]+\.[a-z]+$#", $redirect, $fulluri);
                }
            }

            $parts = bx_collections::getCollectionAndFileParts($fulluri, $mode);
            $collection = $parts['coll'];
        }

        
        $filename = $parts['name'];
        $ext = $parts['ext'];
        $fileNumber = $parts['number'];
        $GLOBALS['POOL']->config->currentFileNumber = $fileNumber; 
        
        if(!isset($_GET["admin"]) && ($collection === FALSE || !$collection->resourceExistsByRequest($filename,$ext) )) {
           
           
           throw new BxPageNotFoundException($this->getAttrib("uri"));
        } else {
            //call postHandles...
            //FIXME: to be implemented...
            if ($filename == "") {
                $plugins = $collection->getPluginMapByRequest("/",$ext);
            } else {
                $plugins = $collection->getPluginMapByRequest($filename,$ext);
            }
            $retcode = 0;
            if (isset($_POST['bx']) && isset($_POST['bx']['plugins'])){
            	
            	foreach($plugins as $id => $plugin) {
                	if (isset($_POST['bx']['plugins'][$plugin['plugin']->name]) && isset($_POST['bx']['plugins'][$plugin['plugin']->name]['_all'])) {
                         $data = bx_helpers_globals::stripMagicQuotes($_POST);
                         foreach ($data['bx']['plugins'][$plugin['plugin']->name] as $name => $value) {
                             $data[$name] = $value;
                             unset ($data['bx']['plugins'][$plugin['plugin']->name][$name]);
                         }
                         unset($data['bx']['plugins'][$plugin['plugin']->name]);
                         if (count($data['bx']['plugins']) == 0) {
                             unset ($data['bx']['plugins']);
                             if (count($data['bx']) == 0) {
                                 unset ($data['bx']);
                             }
                         }
                    	     
                         $retcode = $plugin['plugin']->handlePublicPost($collection->uri,$id,$data);
                    } else if (isset($_POST['bx']['plugins'][$plugin['plugin']->name])) {
                        $data = bx_helpers_globals::stripMagicQuotes($_POST['bx']['plugins'][$plugin['plugin']->name]);
                        
                        $retcode = $plugin['plugin']->handlePublicPost($collection->uri,$id,$data);
                    
                    }
                }
            }
            
            if ($mo) {
                $webrootLang = BX_WEBROOT.'mo/';
            } else {
                $webrootLang = BX_WEBROOT;
            }
            if ($lang != BX_DEFAULT_LANGUAGE) {
                $webrootLang .= $lang."/";
            }
            define('BX_WEBROOT_LANG' ,$webrootLang);
            
            if ($GLOBALS['POOL']->config->dynamicHttpExpires == "true") {
                $expires = bx_resourcemanager::getFirstProperty($collection->uri,"expires");
                if ($expires === NULL) {
                        $expires = 10;
                }
            } else {
                $expires = 10;
            }
            $GLOBALS['POOL']->config->expires = $expires;
            $a =  array(
                
                "collection" => $collection,
                "collectionUri" => $collection->uri,
                "filename" => $filename,
                "ext" => $ext,
                "requestUri" => $fulluri,
                "mode" => $mode,
                'lang' => $lang,
                'locale' => $GLOBALS['POOL']->config->getOutputLocale(),
                'webrootLang' => $webrootLang,
                'fileNumber' => $fileNumber
            );
                         
            $a = array_merge($a,$collection->getPipelineParametersByRequest($filename,$ext));
            $a = array_merge($a,$collection->getPipelineProperties());
            //Do we need that?
            /*
            foreach( $collection->getFirstResource($filename,$ext)->getAllProperties(BX_PROPERTY_PIPELINE_NAMESPACE) as $p) {
                $a[$p['name']] = $p['value'];
            }*/
            if (!isset($a['xslt'])) {
                @session_start();
                if (!($_SESSION['_authsession']['registered'] && isset($_GET['XML']) && $_GET['XML'] == 1)) {
                
                    throw new Exception ("No xslt provided. Either this URL should only be accessed internally and therefore correct, 
                or the sysadmin made a mistake");
                }
            }
            return $a;
        }
    }
}
