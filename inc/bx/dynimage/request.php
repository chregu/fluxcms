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
class bx_dynimage_request {
    
    protected static $partsByRequest = array();
    //public static $basePath = '/dynimage';

    public static function getPipelineByRequest($request) {
        $parts = self::getPartsByRequest($request);
        return $parts['pipeline'];
    }
    
    public static function getParametersByRequest($request) {
        $parts = self::getPartsByRequest($request);
        return $parts['parameters'];
    }
    
    public static function getOriginalFilenameByRequest($request) {
        $parts = self::getPartsByRequest($request);
        return $parts['filename'];
    }
    
    protected static function getPartsByRequest($request) {
        
        /*
            request syntax is:
            /dynimage/method[,param:value,...]/file
        */
        
        if(isset(self::$partsByRequest[$request]))
            return self::$partsByRequest[$request];

        $p = array();
        $i = 0;
        $ms = '';
        $fs = '';

        $token = strtok($request, '/');
        while($token !== FALSE) {
            if($i == 1) 
                $ms = $token;
            if($i > 1)  
                $fs .= '/'.$token;
            $token = strtok('/');
            $i++;
        }
        $p['filename'] = $fs;
        
        // parse pipeline name and parameters
        $m = explode(',', $ms);
        $p['pipeline'] = $m[0];
        $params = array();
        if(sizeof($m > 1)) {
            array_shift($m);
            foreach($m as $param) {
                if(($pos = strpos($param, ':')) !== FALSE) {
                    $pn = substr($param, 0, $pos);
                } else {
                    $pn = $param;
                }
                if(!empty($pn))
                    $params[$pn] = substr($param, $pos+1);
            }
        }
        $p['parameters'] = $params;
        
        self::$partsByRequest[$request] = $p;
        return $p;
    }
    

}

