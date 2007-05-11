<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
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
// | Author: Christian Stocker <chregu@liip.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: bxcms.php 1053 2004-04-08 14:56:51Z philipp $

/**
*
* @author   Christian Stocker <chregu@liip.ch>
* @version  $Id: bxcms.php 1053 2004-04-08 14:56:51Z philipp $
* @package  popoon
*/

class popoon_components_actions_bxcmsadmin extends popoon_components_action {

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
        
        $mode = "admin";
        // prepend /admin/ to fulluri so getCollection is able to find the correct configuration files
        $fulluri = '/admin/' . $this->getAttrib("uri");
        $parts = bx_collections::getCollectionAndFileParts($fulluri, $mode);
        $collection = $parts['coll'] ;
        $filename = $parts['name'];
        $filename = preg_replace("#^/#","",$filename);
         
        if ($parts['number']) {
            $filename = sprintf("%s_%d", $filename, $parts['number']);
        }
        
        $ext = $parts['ext'];

         /*
                if(!empty($_POST['bx']['plugins'][$p->name])) {
                    $p->handlePOST($this->uri,$p['id'], $_POST['bx']['plugins'][$p->name]);
                }
            }
        */
        
        define('BX_WEBROOT_LANG' ,BX_WEBROOT);
        
        if($collection === FALSE) {
            print "not found in admin";
            return array();
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
                    	     
                         $retcode = $plugin['plugin']->handlePost($collection->uri,$id,$data);
                    } else if (isset($_POST['bx']['plugins'][$plugin['plugin']->name])) {
                        $data = bx_helpers_globals::stripMagicQuotes($_POST['bx']['plugins'][$plugin['plugin']->name]);
                        
                        $retcode = $plugin['plugin']->handlePost($collection->uri,$id,$data);
                    
                    }
                }
            }
           /*     if(!empty($_POST['bx']['plugins'][$p->name])) {
                    $p->handlePOST($this->uri,$p['id'], $_POST['bx']['plugins'][$p->name]);
                }
            }*/
            
            
            
            if ($ext) {
                $id = "/$filename.$ext";
            } else {
                $id = "/$filename";
            }

            $dataUri = $collection->getContentUriById($id, true);
            if (DIRECTORY_SEPARATOR != '/') {
                $dataUri = str_replace(DIRECTORY_SEPARATOR,"/",$dataUri);
            }

            $a = array(
            "collection" => $collection,
            "collectionUri" => $collection->uri,
            "collectionUriOfId" => bx_collections::getCollectionUri($id),
            "dataUri" => $dataUri,
            "id" => "$id",
            "lang" => $GLOBALS['POOL']->config->getAdminLanguage(),
            "locale" => $GLOBALS['POOL']->config->getAdminLocale(),
            "returnPostCode" => $retcode
            );
            

            foreach ($collection->getAllProperties(BX_PROPERTY_PIPELINE_NAMESPACE) as $p) {
                $a[$p['name']] = $p['value'];
            }
            foreach( $collection->getSubCollection("/$filename.$ext", 'output')->getAllProperties(BX_PROPERTY_PIPELINE_NAMESPACE) as $p) {
                $a[$p['name']] = $p['value'];
            }
            $a = array_merge($a,$collection->getPipelineParametersByRequest($filename,$ext));
            $a = array_merge($a,$collection->getPipelineProperties());
            return $a;
        }
    }
}
