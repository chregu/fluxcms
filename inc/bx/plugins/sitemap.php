<?php

Class bx_plugins_sitemap extends bx_plugin {

    static private $instance = array();
     
    public static function getInstance($mode) {
        if (!isset(bx_plugins_sitemap::$instance[$mode])) {
            bx_plugins_sitemap::$instance[$mode] = new bx_plugins_sitemap($mode);
        } 
         
        return bx_plugins_sitemap::$instance[$mode];
    } 

    protected function __construct() {
	return true;
    }
   
    
    public function getContentById($path, $id) {
	$expires = (int) $this->getParameter($path, 'cache');
	$cache = bx_helpers_simplecache::getInstance();
	$dom = new DOMDocument();
	if (($xml = $cache->simpleCacheCheck('sitemap', 'plugins', null, 'plain', $expires)) === false) {
	    $sn  = $dom->createElement('sitemap');
            $this->getSitemapTree("/", $dom, $sn);
            $dom->appendChild($sn);
	    $cache->simpleCacheWrite('sitemap', 'plugins', null, $dom->saveXML(), 'plain');
	} else {
	    $dom->loadXML($xml);
	}		

    	return $dom;    
	        
    } 
    
    public function getContent() {
    
    }
    
    
    public function getSitemapTree($path, $domdoc, $domnode, $level = 1) {
        $coll = bx_collections::getCollection($path, $this->mode);
        if (is_object($coll)) {
            
           
           foreach($coll->getChildren($path) as $element => $entry) {
            

               if ($entry->getDisplayName() == 'Files') {
                continue;
               }
               $isCol=false;
               switch($entry->getProperty('output-mimetype')) {
                    
                   case "httpd/unix-directory":
                       
                       $elem = $domdoc->createElement('collection');
                       $elem->setAttribute('level', $level + 1);
                       $isCol=true;
                       
                   break;
                    
                   case "text/html":
                       $elem = $domdoc->createElement('item');
                   break;
                    
               }
                
               if (isset($elem)) {
                    
                    $dn = $domdoc->createElement('display-name');
                    $te = $domdoc->createTextNode(html_entity_decode($entry->getDisplayName(),ENT_NOQUOTES,"UTF-8"));
                    $dn->appendChild($te);
                    
                    if (($order = $entry->getProperty('display-order')) !== NULL) {
                        $do = $domdoc->createElement('display-order');
                        $te = $domdoc->createTextNode($order);
                        $do->appendChild($te);
                        $elem->appendChild($do);
                    }
                    
                    $pa = $domdoc->createElement('path');
                    $te = $domdoc->createTextNode($path);
                    $pa->appendChild($te);
                    
                    $elem->setAttribute('mimetype',$entry->getProperty("output-mimetype")); 
                    $localName = $entry->getLocalName();
                    $elem->setAttribute('uri',$localName); 
                    
                    $elem->appendChild($dn);
                    $elem->appendChild($pa);
                    
                    $domnode->appendChild($elem);
                    
                    if($isCol === true && $localName && $localName != $path) {
                        $this->getSitemapTree($localName, $domdoc, $elem, $level + 1);
                    }
                    
               }
           }
        }
    }

    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        return $path; 
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
}


?>
