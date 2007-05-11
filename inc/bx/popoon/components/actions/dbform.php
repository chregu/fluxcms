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
// $Id$

/**
*
* @author   Silvan Zurbruegg <silvan@liip.ch>
* @version  $Id$
* @package  popoon
*/


class popoon_components_actions_dbform extends popoon_components_action {
    
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
        
        $fulluri = $this->getParameterDefault('id');
        $parts = bx_collections::getCollectionAndFileParts($fulluri,"admin");
        $p = $parts['coll']->getPluginById($parts['rawname']);
        if (!$p) {
            throw new Exception("Plugin for " . $parts['coll']->uri. " , ".$parts['rawname'] ." was not found");
        }
        $r = $p->getResourceById($parts['coll']->uri,$parts['rawname']);
        if (!$r) {
            throw new Exception("Resource for " . $parts['coll']->uri. " , ".$parts['rawname'] ." was not found");
        }
        $id = $r->getInternalDBId();
        //bx_helpers_debug::webdump($id);
        if ($id) {
            return array("table"=>$r->table,
                          "DBid" => $id,
                          );
        }
    }
    
}
?>
