<?php

class bx_editors_blog_sub_files {

    public function __construct() {
        $this->filesRoot = '/files/';
    }

    public function getEditContentById($id) {

        $collectionId = $this->getCollectionIdById($id);
        
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('files'));
        $dom->documentElement->setAttribute('filesRoot', $this->filesRoot);
        $dom->documentElement->setAttribute('collectionId', $collectionId);

        $parts = $this->getParts($collectionId);
        if($parts['coll'] instanceof bx_collection) {
            
            if(isset($_GET['del']) && $_GET['del'] != '') {
                if(($id = $this->getCollectionIdById($_GET['del'])) !== FALSE)
                    $parts['coll']->deleteResourceById($id);
            }
            
            foreach($parts['coll']->getChildren($parts['rawname']) as $child) {
                
                if($child instanceof bx_collection) {
                    $childNode = $dom->createElement('collection');
                } else if($child instanceof bx_resource) {
                    $childNode = $dom->createElement('resource');
                }

                if(isset($childNode)) {
                    $childNode->setAttribute('id', $child->getId());
                    $childNode->setAttribute('mimeType', $child->getMimeType());
                    $childNode->setAttribute('displayName', $child->getDisplayName());
                    $childNode->setAttribute('displayOrder', $child->getDisplayOrder());

                    $dom->documentElement->appendChild($childNode);
                }
            }
        }
        
        return $dom;
    }
    
    public function handlePOST($path, $id, $data) {
        if(isset($data['addFile'])) {
            $coll = bx_collections::getCollection($this->filesRoot.$data['parentCollection'], 'output');
            if ($coll instanceof bx_collection) {
                if(($plugin = $coll->getPluginByResourceType('file')) !== NULL) {
                    $id = $plugin->addResource('', $this->filesRoot.$data['parentCollection'], array(), 'file', TRUE);
                }
            }
        } else if(isset($data['addCollection'])) {
            if(isset($data['collectionUri']) && $data['collectionUri'] != '') {
                $name = bx_helpers_string::makeUri($data['collectionUri']);
                $parentId = $this->filesRoot.$data['parentCollection'];
                $adminPlugin = bx_plugins_admin_collection::getInstance('admin');
                $c = $adminPlugin->makeCollection($parentId.$name);
            }
        }
    }
    
    protected function getParts($id) {
        return bx_collections::getCollectionAndFileParts($this->filesRoot.$id, 'admin');
    }

    protected function getCollectionIdById($id) {
        if(preg_match('#'.$this->filesRoot.'(.+)#', $id, $m)) {
            return $m[1];
        }
        return '';
    }
    
}


