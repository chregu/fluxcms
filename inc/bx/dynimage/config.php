<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dynimage_config {
    
    protected $driver = NULL;
    protected $xpath = NULL;
    protected $pipeline = '';
    protected $dom = NULL;
    
    public function __construct($pipeline) {
        $this->pipeline = $pipeline;
        $configFileName = BX_PROJECT_DIR.'conf/dynimage.xml';
        if(is_readable($configFileName)) {
            $this->dom = new DOMDocument();
            $this->dom->load($configFileName);
            $this->xpath = new DOMXPath($this->dom);
        } else {
            throw new PopoonFileNotFoundException($configFileName);
        }
    }
    
    public function getValidator() {
        $vName = 'filemtime';
        $vNS = $this->xpath->query("/config/pipelines/pipeline[@name='".$this->pipeline."']/validator");
        if($vNS->length > 0) {
            $vName = $vNS->item(0)->getAttribute('type');
        }
        $class = "bx_dynimage_validators_$vName";
        return new $class();
    }
    
    public function getDriver() {
        if(isset($this->driver))
            return $this->driver;
        
        $this->driver = FALSE;

        $drivers = array();
        $dNS = $this->xpath->query("/config/drivers/driver");
        
        foreach($dNS as $dN) {
            $drivers[$dN->getAttribute('priority')] = $dN->getAttribute('type');
        }
        
        ksort($drivers);
        foreach($drivers as $driver) {
            if($this->checkDriver($driver))
                $this->driver = $driver;
        }
        
        return $this->driver;
    }
    
    protected function checkDriver($driver) {
        switch($driver) {
            case 'gd':
                if(get_extension_funcs('gd') !== FALSE)
                    return TRUE;
            break;
            case 'wand':
            case 'magickcmd':
        }
        return FALSE;
    }

    public function getFilters() {
        $driver = $this->getDriver();
        $filters = array();
    
        $vName = 'filemtime';
        $fNS = $this->xpath->query("/config/pipelines/pipeline[@name='".$this->pipeline."']/filter");
        foreach($fNS as $fN) {
            $fName = $fN->getAttribute('type');
            $class = 'bx_dynimage_filters_'.$driver.'_'.$fName;
            $filters[] = new $class();
        }
        return $filters;
    }
    
}

