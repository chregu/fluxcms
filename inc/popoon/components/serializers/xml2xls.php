<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
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
// $Id: xml2xls.php 3244 2004-12-20 11:04:41Z chregu $



/**
* serializes to an excel spreadsheet
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xml2xls.php 3244 2004-12-20 11:04:41Z chregu $
* @package  popoon
*/
class popoon_components_serializers_xml2xls extends popoon_components_serializer {
    
    public $XmlFormat = "XmlString";
    //public $contentType = "application/pdf";
    
    function __construct ($sitemap) {
        
        $this->sitemap = $sitemap;
    }
    
    public function init($attribs) {
        parent::init($attribs);
    }
    
    public function DomStart(&$xml) {
        parent::DomStart($xml);
        if (is_object($xml)) {
            $xmlstr = $xml->saveXML();
        } else {
            $xmlstr = $xml;
            unset ($xml);
        }
       $this->doCache = false;//$this->getParameterDefault("internalCache") != "false";
       $filename = sprintf("%s.%s", $this->getParameterDefault('filename'), $this->getParameterDefault('ext'));
       if ($this->doCache) {
           $this->sc = popoon_helpers_simplecache::getInstance();
           $this->md5 = md5($xmlstr);
           if ($xls = $this->sc->simpleCacheCheck($this->md5,"xml2xls",null,"file",3600)) {
	           $this->sendDownloadHeaders($xmlstr, $filename);
               header("Content-Length: ".filesize($xls));
               readfile($xls);
               return true;
           }
        }
        
        if ($this->getParameterDefault("stripXMLDeclaration") == "true") {
		    $xmlstr = preg_replace("#<\?xml[^>]*\?>\s*#","",$xmlstr);
        }               
        
        $xls = $this->doOnCommandLine($xmlstr);
        if (file_exists($xls)) {
            $this->sendDownloadHeaders($filename, filesize($xls));
            readfile($xls);
	    unlink($xls);
            return true;
        }
    }
    
    protected function sendDownloadHeaders($filename, $fsize) {
        header("Pragma: public");
        //header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Length: ".$fsize);
    }
    
    protected function doOnCommandLine(&$xmlstr) {
        $tmpcsv = tempnam($this->sitemap->cacheDir, "xml2xls-csv-");
        $tmpxls = tempnam($this->sitemap->cacheDir, "xml2xls-xls-");
        file_put_contents($tmpcsv,$xmlstr);
        //echo escapeshellcmd("csv2xls $tmpcsv $tmpxls");
        $returnstr =  exec(escapeshellcmd("csv2xls $tmpcsv $tmpxls"), $error);
        if ($error) {
            print $error;
        }
        
        if ($this->doCache) {
            $this->sc->simpleCacheWrite($this->md5,"xml2xls",null,$tmpxls,"moveFile");
        } 
        
        unlink($tmpcsv);
        return $tmpxls;
    }
    
    
}


?>
