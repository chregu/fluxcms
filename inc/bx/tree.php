<?php



class bx_tree {
    
    private $showUri = true;
    private $showSrc = false;
    private $showPreview = false;
    private $recursive = false;
    private $levelOpen = null;
    private $baseUri = "";
    private $childUris= array();
    private $perm = "";
    private $properties = array();
    
    private $rewrite = false;
    private $rewriteFrom = false;
    private $rewriteTo = false;
    
    
    private $excludePropertyNS = array('bx:', 'bx:de', 'bx:en');
    
    function __construct ($path, $mode, $baseUri = "" ) {
        $this->path = $path;
        $this->mode = $mode;
        $this->baseUri  = $baseUri;
    }
    
    
    public function getXml() {
        if($GLOBALS['POOL']->config->advancedRedirect == 'true'){
            //userdir
            $userdir = bx_resourcemanager::getFirstPropertyAndPath($this->path,'redirect');
            $user = bx_helpers_perm::getUsername();
            if( $userdir !== NULL AND $user != ''){
                $this->rewriteFrom = $userdir['path'].'/'.$user.'/';
                $this->rewriteTo = $userdir['path'].'/';
            }
        }
        
        $coll = bx_collections::getCollection($this->path, $this->mode);
	$colls = array();
        $this->childUris= array();
        $this->childUris[$coll->uri] = "_blabla";
        
        $this->dom = new domDocument();
        $this->dom->loadXML("<collection/>");
        $this->perm = bx_permm::getInstance();
        
        $node = $this->dom->documentElement;
        $level=1;
        if ($this->recursive) {
            while ($coll) {
		array_unshift($colls, $coll);
                $childUri = $coll->uri;
                $coll = $coll->getParentCollection();
                if ($coll) {
                    if ($childUri == $coll->uri)   {
			break;
                    }
                    $this->childUris[$coll->uri] = $childUri;
                }

                
            }
            $this->fillElement($node,bx_collections::getCollection("/",$this->mode));
            
        } else {
            $colls[] = $coll;
            $this->fillElement($node,$coll);
        }
        $node->setAttribute("level",$level);
        $node->setAttribute("selected","selected");
        $node= $node->appendChild($this->dom->createElement("items"));
        
        foreach ($colls as $coll) {
            
            $level++;
            $nextnode = $this->insertChildren($coll,$node,$level);
            
            if($nextnode) {
                $node = $nextnode->appendChild($this->dom->createElement("items"));
            } else {
                break;
            }
        }
        return $this->dom;
    }
    
    protected function insertChildren($coll,$node,$level) {
        
	if (isset($this->childUris[$coll->uri])) {
            $childUri = $this->childUris[$coll->uri];
        } else {
            $childUri = "";
        }
        $nextnode = $el= null;
        
	$displayNamePropertyNS = sprintf('bx:%2s', $GLOBALS['POOL']->config->getOutputLanguage());
	if (is_object($coll) && method_exists($coll, 'getChildren')) {
	    foreach( $coll->getChildren() as $element => $entry) {
	    
	        if (!$this->perm->isAllowed($entry->getId(),array('read_navi', 'read'))) {
                    continue;
                }
                if ($GLOBALS['POOL']->config->advancedRedirect == 'true' AND $entry->getLocalName() == $this->rewriteFrom) {
                    $this->rewrite = TRUE;
                    continue;
                }
            
                $mt = $entry->getProperty("output-mimetype");
                if ($mt == "httpd/unix-directory") {
                    $el = $this->dom->createElement("collection");

                    $displayName = $entry->getProperty('display-name', $displayNamePropertyNS);
                    $displayNameNode = $this->dom->createElement('display-name');
		    $displayNameNode->appendChild($this->dom->createTextNode(html_entity_decode($displayName, ENT_NOQUOTES, 'UTF-8')));
                    $el->appendChild($displayNameNode);

                } elseif (in_array($mt, $this->mimetypes)) {
                   $el = $this->dom->createElement("resource");
                } else {
                    continue;
                }
            
                foreach($this->properties as $prop) {
                    $el->setAttribute($prop,$entry->getProperty($prop));   
                }
           	 
                $this->fillElement($el, $entry, $coll->uri);
                $newnode = $node->appendChild($el);
                $newnode->setAttribute("level",$level); 
                if ($entry->getLocalName() == $childUri) {
                    $newnode->setAttribute("selected","selected");
                    $nextnode = $newnode; 
                } else if ($mt == "httpd/unix-directory" && $this->levelOpen && $level < $this->levelOpen) {
		    //$this->insertChildren($entry,$el->appendChild($this->dom->createElement("items")),$level + 1);
            
		    $this->insertChildren($entry,$el->appendChild($this->dom->createElement("items")), $level + 1);
	        }
            }
        }
   
        return $nextnode;
    }
    
    
    protected function fillElement($el, $entry, $uri = "/") {        
        
        $displayname = $this->dom->createElement("title");
        $el->appendChild($displayname);
        
        $te = $this->dom->createTextNode(html_entity_decode($entry->getDisplayName(),ENT_NOQUOTES,"UTF-8"));
        
        //filename
        
        if ((! ($entry instanceof bx_collection)) && $entry instanceof bxIresource ) {
            $el->appendChild($this->dom->createElement("filename",$entry->getFileName()));
        }
        
        //$ln = $this->dom->createElement("localname",$entry->getLocalName());
        //$el->appendChild($ln);
        $mt = $entry->getProperty("output-mimetype");
        if ($relink = $entry->getProperty("relink")) {
            $el->setAttribute("relink",$relink);
        }
        // userdir
        if($this->rewrite){
            switch($mt){
                case 'httpd/unix-directory':
                $relink = str_replace($this->rewriteFrom, $this->rewriteTo, $entry->getLocalName() );                    
                break;
                case 'text/html':
                $relink =  $entry->getFileName().'.html' ;
                break;
            }
            $el->setAttribute("relink",$relink);
        }
        
        $el->setAttribute('lang',$entry->getLanguage());
        
        
        
        $el->setAttribute('mimetype',$mt); 
        //$el->setAttribute('id',$entry->getId());
        
        if ($mt == "httpd/unix-directory") {
            if ($this->showUri) {
                $el->appendChild($this->dom->createElement('uri', $entry->getLocalName()));
            } 
            if ($this->showSrc) {
                $el->appendChild($this->dom->createElement('src',$this->baseUri. $entry->getLocalName()));
            }
        } 
        
        else { 
            if ($this->showUri) {
                $el->appendChild($this->dom->createElement('uri',$uri .$entry->getLocalName()));
            }
            if ($this->showSrc) {
                $el->appendChild($this->dom->createElement('src',$this->baseUri."/". $entry->getLocalName()));
            }
        }
        
        if ($this->showPreview) {
            $el->appendChild($this->dom->createElement('preview',$uri.$entry->getLocalName()));
        }
        
        $displayname->appendChild($te);
        $do = $entry->getDisplayOrder();
        if ($do !== NULL) {
            $el->appendChild($this->dom->createElement('display-order', $do));
        }
        if ($do = $entry->getDisplayImage()) {
            $el->appendChild($this->dom->createElement('display-image', $do));
        }
        
        $propNode = $this->getPropertiesNode($entry->getAllProperties());
        if($propNode->hasChildNodes()) {
            $el->appendChild($propNode);
        }
        
        return $el;
    }
    
    
    public function setMimeTypes($mimetypes ) {
        $this->mimetypes = $mimetypes;
    }
    
    public function setProperties($props) {
        $this->properties = $props;   
    }
    
    public function setElements($elem) {
        if (in_array('uri', $elem)) {
            $this->showUri = true;
        } else {
            $this->showUri = false;
        }
        if (in_array('src',$elem)) {
            $this->showSrc = true;
        }
        if (in_array('preview',$elem)) {
            $this->showPreview = true;
        }
        
    }
    
    public function  setLevelOpen($lo = null) {
        if ($lo) {
            $this->levelOpen = $lo;
        }
    }
    
    public function setRecursive ($set) {
        $this->recursive = $set;
    }
    
    protected function getPropertiesNode($properties) {
        $propertiesNode = $this->dom->createElement('properties');
        foreach($properties as $key => $property) {
            if(!in_array($property['namespace'], $this->excludePropertyNS)) {
                $propNode = $this->dom->createElement('property');
                $propNode->setAttribute('namespace', $property['namespace']);
                $propNode->setAttribute('name', $property['name']);
                $propNode->setAttribute('value', $property['value']);
                $propertiesNode->appendChild($propNode);
            }
        }
        return $propertiesNode;
    }
}
