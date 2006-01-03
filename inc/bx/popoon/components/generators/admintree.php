<?php

class popoon_components_generators_admintree extends popoon_components_reader {
    
    private $requestUri = "";
    
    
    function __construct ($sitemap) {
        parent::__construct($sitemap);
        $this->requestUri = str_replace('//', '/', $_SERVER['REQUEST_URI']);
    }
    
   
    function init($attribs) {
        parent::init($attribs);
    } 
    
    
    public function DomStart(&$xml) {
        
        if (!empty($this->requestUri)) {
            
            $strpos = strpos($this->requestUri,"/admin/navi/tree");
            $path = substr($this->requestUri,$strpos+16);
            if (!$path) {
                $path = '/';
            }
             /* strip GET params for collection lookup */
            $path = preg_replace("/\?.*$/", "", $path);
            $outputPath = $path;
            // FIXME: No hardcoded Paths, and no preg_replace'ing them 
            if (preg_match("/\/data\//", $path)) {
                $path = preg_replace("/\/data/", "", $path);
                
            }
            
           
        
        }
        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));

        $p = bx_collections::getCollectionAndFileParts($path, "output");
        $coll = $p['coll'];
        
        $dom = new domDocument();
        $dom->loadXML("<navitree/>");
        
        $pathNode = $dom->createElement('path');
        $pathNode->appendChild($dom->createTextNode($outputPath));
        $dom->documentElement->appendChild($pathNode);
        $params = $this->getGetParamsNode($dom);
        $dom->documentElement->appendChild($params);
     //   $dom->documentElement->appendChild());
        $ch = $coll->getChildren($p['rawname']) ;
        
        usort($ch,array("popoon_components_generators_admintree","sortByRang"));
        foreach( $ch as $element => $entry) {
            
            $el = $dom->createElement("item");
            $el->setAttribute('mimetype',$entry->getProperty("output-mimetype")); 
            $el->setAttribute('uri',$entry->getLocalName()); 
               
            switch ($entry->getProperty("output-mimetype")) {
                case "httpd/unix-directory":
                    $el->setAttribute('iconAction',"testopen('".$coll->uri.$p['rawname'].$entry->getBaseName()."/',this)");
                    $el->setAttribute('icon', BX_WEBROOT.'admin/webinc/img/icons/fileicon_folder.gif');
                    $el->setAttribute('openIcon', BX_WEBROOT.'admin/webinc/img/icons/fileicon_folder.gif');
                    $el->setAttribute('title',$entry->getDisplayName());
                    $el->setAttribute('src', BX_WEBROOT.'admin/navi/tree'.$entry->uri);
                    $el->setAttribute('name', $entry->getBaseName());
                    $el->setAttribute('action',BX_WEBROOT. 'admin/overview'.$coll->uri.$p['rawname'].$entry->getBaseName().'/');
                    
                    if($entry->getBaseName() == 'themes' AND $permObj->isAllowed('/',array('admin'))) {
                        $dom->documentElement->appendChild($el);
                    } else if($entry->getBaseName() != 'themes') {
                        $dom->documentElement->appendChild($el);
                    }
                
                break;

                default:
                    $mimetype = $entry->getMimeType();
                    $el->setAttribute('iconAction',"testopen('".$coll->uri.$p['rawname'].$entry->getLocalName()."',this)");
                    $el->setAttribute('action', BX_WEBROOT.'admin/overview'.$coll->uri.$p['rawname'].$entry->getLocalName());
                    $el->setAttribute('icon', BX_WEBROOT.'admin/webinc/img/icons/'.$mimetype.'.gif');
                    $el->setAttribute('openIcon', BX_WEBROOT.'admin/webinc/img/icons/'.$mimetype.'.gif');
                    $el->setAttribute('title',$entry->getDisplayName());
                    $el->setAttribute('name', $entry->getLocalName());
                    $dom->documentElement->appendChild($el);
                    
                
            }
            
            
         }
        
        $xml = $dom;
    }
    
    
    static function sortByRang($a,$b) {
            
            $aIsDir = ($a->getMimeType() == "httpd/unix-directory") ?true :false;
             $bIsDir = ($b->getMimeType() == "httpd/unix-directory") ?true :false;
            
            if ($aIsDir && !($bIsDir)) {
                
                return -1;
            }
            
            if ($bIsDir && !($aIsDir)) {
                return 1;
            }
            $ad = $a->getDisplayOrder();
            $bd = $b->getDisplayOrder();
            
            if ($ad == $bd) {
                return (strtolower($a->getLocalName()) < strtolower($b->getLocalName())) ? -1 : 1;
            
            };
            if (!$ad) {
                return 1;
            }
            if (!$bd) {
                return -1;
            }
            return ($ad < $bd) ? -1 : 1;

    }
    
    
    protected static function getGetParamsNode(&$dom) {
        
        $params = $dom->createElement('params');
        
        foreach($_GET as $name => $value) {
       //     echo $name."00".$value."<br/>";
            $param = $dom->createElement('param');
            $param->setAttribute('name', $name);
            $param->setAttribute('value', $value);
            $params->appendChild($param);
        }
        
        return $params;
    }
    
}

?>
