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
* Class for aggregating content
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_aggregator extends popoon_component {
    public $xml = "";
    
    function __construct(&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    /**
    * Initiator, called after construction of object
    *
    *  This method will be called in the start element with the attributes from this element
    *
    *  @param $attribs array	associative array with element attributes
    *  @access public
    */
    function init($attribs) {
        parent::init($attribs);
        
        $rootElement = $this->getAttrib("element");
        $rootNs = $this->getAttrib("ns",array("http"));
        $rootPrefix = $this->getAttrib("prefix");
        $this->xml = new DomDocument();
        
        if ($rootNs) {
            $this->xmlRoot = $this->xml->appendChild($this->xml->createElementNs($rootNs,$rootElement));
        } else {
            $this->xmlRoot = $this->xml->appendChild($this->xml->createElement($rootElement));
        }
    }
    
    function start(&$xml) {
        $xml = $this->xml;
    }
    
    function addPart($attribs ,$xmlInput = null) {
        
        if (isset($attribs["src"])) {
            $src = $this->sitemap->translateScheme($attribs["src"],array("http"));
        } else {
            $src = "";
        }
        $stripRoot = false;
        if (isset($attribs["strip-root"])) {
            $stripRoot = $this->sitemap->translateScheme($attribs["strip-root"]);
        }
        $createElement = false;
        if (isset($attribs["element"])) {
            $createElement =  $this->sitemap->translateScheme($attribs["element"]);
        }
        if ($xmlInput)  {
            $xmldoc = $xmlInput;
        }
        else if (strpos($src,"popoon:") === 0 ) {
            
            $uri = $this->sitemap->translateScheme(substr($src,7));
            if ($uri == $this->sitemap->uri) {
                popoon::raiseError("to be aggregated uri ($uri) is the same as the calling sitemap-uri (".
                $this->sitemap->uri ."). This will lead to infinite recursion..!",
                POPOON_ERROR_FATAL,
                __FILE__, __LINE__,  null);
                
            }
            $options = $this->sitemap->getOptions(true);
            $options->internalRequest = true;
            $options->popoonmap = $this->getParameter("popoonmap");

            ob_start();
            $sitemap = new popoon_sitemap($this->sitemap->rootFile, $uri, $options);
 
            /* if the serializer thinks, its object in $sitemap->xml is the right one, it
            can set hasFinalDom to true and we can take just this and don't have to
            parse it again, otherwise get the outputtet content and parse it to a dom */
            
            if (isset($sitemap->hasFinalDom) && $sitemap->hasFinalDom && $sitemap->xml instanceof  domdocument) {
                
                $xmldoc = $sitemap->xml;
            } else {
                $xmldoc = new DomDocument();
                $err = $xmldoc->loadXML(ob_get_contents());
                if (!$err) {
                    $root = $xmldoc->createElement("popoon");
                    $root->appendChild($xmldoc->createTextNode("XML Parsing Error for $src"));
                    $xmldoc->appendChild($root);
                }
                    
            }
            ob_end_clean();
        }
        // if protocol is http:// get it :)	
        
        else if (strpos($src,"http:") === 0 ) {
            //FIXME: fopen_allow_url = Off safe ... (use http request from PEAR)
            $xmldoc = new DomDocument();
            $xmldoc->loadXML(implode("\n",file($src)));
            
        } else {            
            popoon::raiseError("map:part does not handle any other protocol than popoon: and http: or map:generate as child right now",
            POPOON_ERROR_FATAL,
            __FILE__, __LINE__,  null);
        }
        
        $root = $xmldoc->documentElement;
        if ($stripRoot == "true") {
            $children = $root->childNodes;
            foreach ($children as $child) {
                $this->xmlRoot->appendChild($this->xml->importNode($child,true));
            }
        } elseif ($createElement != false) {
            $newElement = $this->xml->createElement($createElement);
            $newElement->appendChild($this->xml->importNode($root,true));
            $this->xmlRoot->appendChild($newElement);
        } else {
            
            $this->xmlRoot->appendChild($this->xml->importNode($root,true));
        }
    }
}
