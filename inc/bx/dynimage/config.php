<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dynimage_config {
    
    protected $driver = NULL;
    protected $xpath = NULL;
    public $request = NULL;
    protected $pipeline = '';
    protected $dom = NULL;
    protected $driversByPriority = NULL;
    
    public function __construct($request) {
        $this->request = $request;
        $this->pipeline = bx_dynimage_request::getPipelineByRequest($request);
        
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

        $this->driversByPriority = array();
        $dNS = $this->xpath->query("/config/drivers/driver");
        
        foreach($dNS as $dN) {
            $this->driversByPriority[$dN->getAttribute('priority')] = $dN->getAttribute('type');
        }
        
        ksort($this->driversByPriority);
        foreach($this->driversByPriority as $driver) {
            if($this->checkDriver($driver)) {
                $class = "bx_dynimage_drivers_$driver";
                $this->driver = new $class();
            }
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
    
    public function getNextDriver($currentDriver) {
    }

    public function getFilters() {
        $driver = $this->getDriver()->name;
        $filters = array();
    
        $fNS = $this->xpath->query("/config/pipelines/pipeline[@name='".$this->pipeline."']/filter");
        foreach($fNS as $fN) {
            $fName = $fN->getAttribute('type');
            $class = 'bx_dynimage_filters_'.$driver.'_'.$fName;
            $filter = new $class();
            $filterParameters = $this->getFilterParameters($fN);
            $filter->setParameters($filterParameters);
            $filters[] = $filter;
            
        }
        return $filters;
    }
    
    protected function getFilterParameters($node) {
        $parameters = array();
        $pNS = $this->xpath->query("parameter", $node);
        $dynamicParameters = bx_dynimage_request::getParametersByRequest($this->request);
        foreach($pNS as $parameter) {
            $pName = $parameter->getAttribute('name');
            $parameters[$pName] = $parameter->getAttribute('value');
            if(preg_match('#\{(.*)\}#', $parameters[$pName], $matches)) {
                if(isset($dynamicParameters[$matches[1]]))
                    $parameters[$pName] = $dynamicParameters[$matches[1]];
            }
        }
        return $parameters;
    }
    
}

