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
/**
* class bx_plugin
* @package bx_plugins
*/

abstract class bx_plugin extends bx_component implements bxIplugin {
    protected $mode;
    
    protected function __construct($mode = "output") {
        
        $this->mode = $mode;
    }
    
    function __destruct() {
        if (isset($this->res) && is_array($this->res)) {
            foreach($this->res as $res) {
                unset($res);   
            }
        }
    }
    
    public function getChildren($uri, $id) {
        return array();
    }

    /** @bxIplugin::getIdByRequest*/
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "$name.$ext.".$this->name;
    } 
    
    public function getContentById($path, $id) {
        return null; 
    }
    
    public function getEditorsById($path, $id) {
        $resourceEditors = array();

        $res = $this->getResourceById($path,$id);
        if ($res instanceof bxIresource) {
            $resourceEditors = $res->getEditors();
        } else {
            // if no resource was found, try with a new one
            $res = $this->getResourceById($path, $id, TRUE);
            if ( $res instanceof bxIresource) {
                $resourceEditors = $res->getEditors();
            }
        }
        
        $pluginName = str_replace('bx_plugins_', '', get_class($this));
        $pluginEditors = $GLOBALS['POOL']->config->getEditorsByPlugin($pluginName);
        if(!empty($pluginEditors)) {
            $editors = array_intersect($pluginEditors, $resourceEditors);
        } else {
            $editors = $resourceEditors;
        }
        return $editors;
    }

    public function getMimeTypes() {
        return array();
    }
    
    public function getResourceTypes() {
        return array();
    }
    
    public function isRealResource($path, $id) {
        return false;
    }
    
    public function adminResourceExists($path, $id, $ext=null) {
        return false; 
    }
    
    /** @bxIplugin::getContentUriById */
    public function getContentUriById($path, $id, $sample = false) {
        //FIXME... we do not have a Resource in the DB eventually... 
        // take care of that here
        $res = $this->getResourceById($path,$id, $sample);
        if ($res instanceof bxIresource ) {
            if ($sample && $res->mock) {
                return $res->getContentUriSample();
            } else {
                return $res->getContentUri();
            }
        } else {
            return NULL;
        }
    } 
    
    /** @bxIplugin::getResourceById 
     * 
     *  NB: interface violation: adding mock
     *  For weird situations when resource requested before created.
     *  The only found call is $this->getEditorsById()
     *  
     */
    public function getResourceById($path, $id, $mock = false) {
        return null;
    }
    
    public function resourceExistsById($path,$id) {
        
        if ($this->getResourceById($path,$id) instanceof bxIresource) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns plugin-dependant parameters to pass into pipeline for specified resource
     *
     * @param path collection path
     * @param id resource id 
     * @return array of parameters
     */
    public function getPipelineParametersById($path, $id) {
            return array();
    }
    
    public function stripRoot() {
        return false;
    }
    
    public function addResource($name, $parentUri, $options=array(), $resourceType = null) {
        return false;
    }
        
    public function deleteResourceById($path, $id) {
        $r = $this->getResourceById($path, $id);
        if (($r instanceof bx_resource) &&
            method_exists($r,"delete")) {
                 
            return $r->delete();
        
        }
        return array();
    }
    
    public function copyResourceById($path, $id, $to, $move = false) {
        $r = $this->getResourceById($path, $id);
        if (($r instanceof bx_resource) &&
            method_exists($r,"copy")) {
            return $r->copy($to, $move);
        }
        return array();
    }
    
    /**
     * @return unix timestamp
     */
    public function getLastModifiedById($path, $id) {
        $res = $this->getResourceById($path,$id);
        if ($res) {
            return  $res->getLastModified();
        } 
        return NULL;
        
    }
    
    public function collectionCopy($point, $from, $to, $move) {
        return true;   
    }
    
    public function collectionDelete($point, $dir) {
        return true;   
    }
    
    public function getOverviewSections($path) {
        return null;
    }
    
    protected function setOverviewTitle($dom,$title) {
        
    }
    
    public function getPermissionList() {
    	return array();	
    }
}

?>
