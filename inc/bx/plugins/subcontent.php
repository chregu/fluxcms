<?php

class bx_plugins_subcontent extends bx_plugin {
    
    static private $instance = array();
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_subcontent::$instance[$mode])) {
            bx_plugins_subcontent::$instance[$mode] = new bx_plugins_subcontent($mode);
        } 
        return bx_plugins_subcontent::$instance[$mode];
    }   
    
    
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "$name";
    }
    
    public function getContentById($path, $id) {
        $this->mimetypes=array("text/html");
        
        $coll = bx_collections::getCollection($path, $this->mode);
        
        $this->dom = new domDocument();
        $this->appendCollection($coll);

        return $this->dom;
        
    }
    
    protected function appendCollection($collection, $superNode = NULL) {
        $id = $collection->getID();

        $collectionNode = $this->dom->createElement('collection');
        $this->appendProperties($collection, $collectionNode, '');
        
        $childrenNode = $collectionNode->appendChild($this->dom->createElement('items'));
        
        if(isset($superNode)) {
            $superNode->appendChild($collectionNode);
        } else {
            $this->dom->appendChild($collectionNode);
            $superNode = $this->dom->documentElement;
        }

        foreach($collection->getChildren($id) as $i => $child) {
            $mt = $child->getProperty("output-mimetype");
            if($mt == 'httpd/unix-directory') {
                $this->appendCollection($child, $childrenNode);
            } else if(in_array($mt, $this->mimetypes)) {
                //$childrenNode->appendChild($this->dom->createElement('resource'));
                $this->appendResource($child, $childrenNode, $collection->uri);
            } else {
                continue;
            }
        }
        
    }
    
    protected function appendResource($resource, $superNode, $uri) {
        $resourceNode = $this->dom->createElement('resource');
        $superNode->appendChild($resourceNode);
        $this->appendProperties($resource, $resourceNode, $uri);

        $subdom = new DomDocument();
        $subdom->load($resource->getContentUri());
        $subdom->xinclude();
        $newnode = $this->dom->importNode($subdom->documentElement, TRUE);
        $newnode = $resourceNode->appendChild($newnode);
    }
    
    protected function appendProperties($child, $superNode, $uri = '/') {
        $superNode->setAttribute('lang', $child->getLanguage());
        
        $displayname = $this->dom->createElement("title");
        $superNode->appendChild($displayname);
        $te = $this->dom->createTextNode(html_entity_decode($child->getDisplayName(),ENT_NOQUOTES,"UTF-8"));
        $displayname->appendChild($te);
        
        $superNode->appendChild($this->dom->createElement('uri', $uri.$child->getLocalName()));        
        $superNode->appendChild($this->dom->createElement('display-order', $child->getDisplayOrder()));
    }
        
    public function resourceExists($path, $name, $ext) {
        return true;
    }

    public function isRealResource($path , $id) {
        return true;
    }
    
    
}

?>
