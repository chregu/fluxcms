<?php

class bx_editors_blog_sub_gallery {

    protected $absoluteRoot = '';
    protected $virtualRoot = '';
    static protected $currentImageId = FALSE;
    static protected $switchToTab = 'properties';
    
    public function __construct() {
        $this->virtualRoot = '/files/_galleries/gallery/';
        $this->absoluteRoot = BX_OPEN_BASEDIR.$this->virtualRoot;
        $this->baseCollection = '/gallery/';
    }
    
    public function getEditContentById($id) {
        $galleryId = $this->getGalleryById($id);

        // check for an image to be deleted
        if(isset($_GET['del']) && $_GET['del'] != '') {
            $parts = bx_collections::getCollectionAndFileParts($_GET['del'], 'admin');
            $parts['coll']->deleteResourceById($parts['rawname']);
        
        } else if(isset($_GET['gdel']) && $_GET['gdel'] != '') {
        
            $gid = $_GET['gdel'];
            $parts = bx_collections::getCollectionAndFileParts($gid, 'admin');
            $parts['coll']->deleteResourceById($parts['rawname']);
        }
        
        $parts = bx_collections::getCollectionAndFileParts($this->baseCollection.$galleryId, 'admin');
        
        $images = $this->getImagesByGalleryId($galleryId);
        $images->documentElement->setAttribute('virtualRoot', $this->virtualRoot);
        $images->documentElement->setAttribute('baseCollection', $this->baseCollection);
        $images->documentElement->setAttribute('galleryId', $galleryId);
        
        // add subgalleries to the dom
        if(isset($parts['coll']) && $parts['coll'] instanceof bx_collection) {
            
            foreach($parts['coll']->getChildren() as $child) {
                if($child instanceof bx_collection) {
                    $collNode = $images->createElement('collection');
                    $collNode->setAttribute('id', $child->id);
                    $images->documentElement->appendChild($collNode);
                }
            }
        }
        
        if(self::$currentImageId !== FALSE) {
            $images->documentElement->setAttribute('currentImageId', self::$currentImageId);
        } else {
            if(isset($_GET['id']) && !isset($_GET['del'])) {
                self::$currentImageId = $_GET['id'];    
            } else {
                self::$currentImageId = $images->documentElement->firstChild->getAttribute('id');
            }
            $images->documentElement->setAttribute('currentImageId', self::$currentImageId);
        }
        $images->documentElement->setAttribute('switchToTab', self::$switchToTab);
        
        return $images;
    }

    public function handlePOST($path, $id, $data) {
        if(isset($data['id'])) {
            self::$currentImageId = $data['id'];
            
            $id = $data['id'];
            
            foreach($GLOBALS['POOL']->config->getOutputLanguages() as $lang) {
                if(isset($data['title'][$lang])) {
                    bx_resourcemanager::setProperty($id, 'title', $data['title'][$lang], 'bx:'.$lang);
                }

                if(isset($data['description'][$lang])) {
                    bx_resourcemanager::setProperty($id, 'description', $data['description'][$lang], 'bx:'.$lang);
                }
                
            }
            
        } else if(isset($data['addImage'])) {
            
            $galleryId = $this->getGalleryById($id);
            $coll = bx_collections::getCollection($this->baseCollection.$galleryId, 'admin');
            if ($coll instanceof bx_collection) {
                if(($plugin = $coll->getPluginByResourceType('file')) !== NULL) {
                    $id = $plugin->addResource('', $this->baseCollection.$galleryId, array(), 'file', TRUE);
                    // cut off parent uri
                    $id = $this->virtualRoot.substr($id, strlen($this->baseCollection));
                    if($id)
                        self::$currentImageId = $id;
                }
            }
        
        } else if(isset($data['addGallery']) && isset($data['gallery']) && $data['gallery'] != '') {
            self::$switchToTab = 'creategallery';
            $galleryId = $this->getGalleryById($id);
            $coll = bx_collections::getCollection($this->baseCollection.$galleryId, 'admin');
            if ($coll instanceof bx_collection) {
                if(($plugin = $coll->getPluginByResourceType('file')) !== NULL) {
                    $id = $plugin->addResource('none', $this->baseCollection.$galleryId, array('collection' => $data['gallery']), 'gallery', TRUE);
                    // cut off parent uri
                    $id = substr($id, strlen('/'.$this->virtualRoot));
                    if($id)
                        self::$currentImageId = $id;
                }
            }
        }
    }
    
    protected function getGalleryById($id) {
        // remove an ev. dot at the end
        if(substr($id, -1, 1) == '.') {
            $id = substr($id, 0, -1);
        }
        
        if(preg_match('#sub/gallery/(.*)#', $id, $m)) {
            return $m[1];
        }
        
        return '';
    }
    
    protected function getImagesByGalleryId($galleryId) {
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('images'));
        
        $root = $this->absoluteRoot.$galleryId;
        
        if (file_exists($root)) {
            $dir = new DirectoryIterator($root);
            foreach ($dir as $file) {
                $name = $file->getFileName();
                
                if (strpos($name,".") === 0) {
                    continue;
                }
                
                if ($file->isDir()) {
                } else  {
                    $image = $dom->createElement('image');
                    $image->setAttribute('href', $name);
                    $image->setAttribute('id', $this->virtualRoot.$galleryId.$name);
                    
                    $titleNode = $dom->createElement('title');
                    $descrNode = $dom->createElement('description');
                    
                    foreach($GLOBALS['POOL']->config->getOutputLanguages() as $lang) {
                        $title = bx_resourcemanager::getProperty($this->virtualRoot.$galleryId.$name, 'title', 'bx:'.$lang);
                        $descr = bx_resourcemanager::getProperty($this->virtualRoot.$galleryId.$name, 'description', 'bx:'.$lang);
                        
                        $titleTN = $dom->createElement($lang);
                        $titleTN->appendChild($dom->createTextNode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')));
                        $descrTN = $dom->createElement($lang);
                        $descrTN->appendChild($dom->createTextNode(html_entity_decode($descr, ENT_COMPAT, 'UTF-8')));
                        
                        $titleNode->appendChild($titleTN);
                        $descrNode->appendChild($descrTN);
                    }
                    $image->appendChild($titleNode);
                    $image->appendChild($descrNode);
                    
                    $dom->documentElement->appendChild($image);
                }
            }
        }
        
        $langsNode = $dom->createElement('outputLanguages');
        foreach($GLOBALS['POOL']->config->getOutputLanguages() as $lang) {
            $langNode = $dom->createElement('language', $lang);
            $langNode->setAttribute('language', $lang);
            $langsNode->appendChild($langNode);
            
        }
        $dom->documentElement->appendChild($langsNode);
        
        return $dom;
    }

}

