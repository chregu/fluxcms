<?php

class bx_plugins_admin_listview extends bx_plugin {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_listview::$instance) {
            bx_plugins_admin_listview::$instance = new bx_plugins_admin_listview($mode);
        } 
        return bx_plugins_admin_listview::$instance;
    }
    
    public function getContentById($path, $id) {
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('listview'));
        
        $dom->documentElement->setAttribute('id', $id);
        
        $parts  = bx_collections::getCollectionAndFileParts($id, $this->mode);
        $coll = $parts['coll'];
        $id = $parts['rawname'];

        if($coll instanceof bx_collection) {
            $children = $coll->getChildren();
            
            foreach($children as $child) {
                $childNode = NULL;
                if($child instanceof bx_collection) {
                    $childNode = $dom->createElement('collection');
                } else if($child instanceof bx_resource){
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

    protected function getParentCollection($path) {
        $parent = dirname($path);
        if ($parent != "/"){
            $parent .= '/';
        }
        return bx_collections::getCollection($parent,"output");
    }

    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getDataUri($path, $name, $ext) {
        return FALSE;
    }

    public function getEditorsByRequest($path, $name, $ext) {
        return array();   
    }

    public function getStylesheetNameById($path = NULL, $id = NULL) {
        return 'listview.xsl';
    }
    
    public function getPipelineName($path = NULL, $name = NULL, $ext = NULL) {
        return "standard";
    }

    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
    
}
?>
