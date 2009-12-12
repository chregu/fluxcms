<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Carl Hasselskog <Carl@calle.nu>                              |
// |         Christian Stocker <chregu@liip.ch>                        |
// +----------------------------------------------------------------------+


Class bx_plugins_fulltree extends bx_plugin {
    
    
    static private $instance = array();
    private $currentPage;
    private $currentCategory;
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_fulltree::$instance[$mode])) {
            bx_plugins_fulltree::$instance[$mode] = new bx_plugins_fulltree($mode);
        } 
        
        return bx_plugins_fulltree::$instance[$mode];
    } 
    
    protected function __construct() {
        return true;
    }
    
    
    public function getContentById($path, $id) {
        
        $dom = new domDocument();
        $sn  = $dom->createElement('sitemap');
        
        //Add selected attribute to all collections in the selection-path
        $selectedColls = Array();
        $coll = bx_collections::getCollection($path, $this->mode);
        $this->currentPage = $coll->uri;
        $starturi = $this->getParameter($path,"starturi");
        if (!$starturi) {
            $starturi = '/';
        }
        $this->getSitemapTree($starturi, $dom, $sn, $selectedColls);
        
        $dom->appendChild($sn);
        return $dom;
        
    } 
    
    public function getContent() {
        
    }
    
    
    public function getSitemapTree($path, $domdoc, $domnode, $selectedColls) {
        $coll = bx_collections::getCollection($path, $this->mode);
        $itemsElem = $domdoc->createElement('items');
        if (is_object($coll)) {
            foreach($coll->getChildren($path) as $element => $entry) {
                if ($entry->getDisplayName() == 'Files') {
                    continue;
                }
                $isCol=false;
                switch($entry->getProperty('output-mimetype')) {
                    
                    case "httpd/unix-directory":
                    
                    $elem = $domdoc->createElement('collection');
                    if(strpos($this->currentPage, $entry->uri) === 0 ) 
                    {
                        $elem->setAttribute('selected','selected');
                    }
                  
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
                    $elem->setAttribute('lang',$entry->getLanguage()); 
                    
                    $localName = $entry->getLocalName();
                    $uri = $domdoc->createElement('uri');
                    $te = $domdoc->createTextNode($localName);
                    $uri->appendChild($te);
                    
                    $elem->appendChild($dn);
                    $elem->appendChild($pa);
                    $elem->appendChild($uri);
                    
                    $itemsElem->appendChild($elem);
                    $domnode->appendChild($itemsElem);
                    if ($isCol === true && $localName && $localName != $path) {
                        $this->getSitemapTree($localName, $domdoc, $elem, $selectedColls);
                        
                    }
                    
                }
                
            }
        }
        
    }
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        return $path.".fulltree"; 
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
}


?>
