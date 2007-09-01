<?php
/**
 * assets table structure:
 * +--------+-------------------------+------+-----+---------+----------------+
 * | Field  | Type                    | Null | Key | Default | Extra          |
 * +--------+-------------------------+------+-----+---------+----------------+
 * | id     | int(10) unsigned        |      | PRI | NULL    | auto_increment |
 * | path   | varchar(255)            | YES  | MUL | NULL    |                |
 * | name   | varchar(255)            | YES  |     | NULL    |                |
 * | value  | varchar(255)            | YES  |     | NULL    |                |
 * | type   | enum('link','download') | YES  |     | link    |                |
 * | lang   | char(2)                 | YES  | MUL | NULL    |                |
 * | target | varchar(20)             | YES  |     | NULL    |                |
 * +--------+-------------------------+------+-----+---------+----------------+
 * 
 */

class bx_editors_assets extends bx_editor implements bxIeditor {
    
    private $assetTable = 'assets';
    private $prepqAssetTypes = array('text','text','text','text','text','text');
    
    private $db = null;
    
    
    public function __construct() {
        $this->assetTable = $GLOBALS['POOL']->config->getTablePrefix().$this->assetTable;
        $this->db = $GLOBALS['POOL']->db;
        
    }
     
    public function getDisplayName() {
        return 'Assets Editor';
    }
    
    public function getPipelineParametersById($path, $id) {
		return array('pipelineName'=>'assets');
    }   
    
    
    public function getEditContentById($id) {
        $content = new DomDocument();
        if ($content instanceof DOMDocument) {
            $content->loadXML('<assets/>');    
            
            $content->documentElement->setAttribute('path', $id);
            
            $langNode = $content->createElement('langs');
            $content->documentElement->appendChild($langNode);
            bx_helpers_xml::array2Dom($GLOBALS['POOL']->config->getOutputLanguages(), $content, $langNode);
            
             
            $assets = $this->getAssetsById($id);
            
            if (is_array($assets)) {
                bx_helpers_xml::array2Dom($assets, $content, $content->documentElement);
            
                
                 
            }
        
            $parentResources = array();
            $childResources = array();
            
            $parts = bx_collections::getCollectionAndFileParts($id,"admin");
            if (isset($parts['coll'])) {
                
                $coll = $parts['coll'];
                
                /* Get parent resources */
                $parent = $coll->getParentCollection();
                if ($parent instanceof bx_collection) {
                    
                    $children = $parent->getChildren();
                    if (is_array($children)) {
                        
                        foreach($children as $child) {
                            if ($child instanceof bx_resource && $child->getMimeType() == 'text/html') {
                                
                                $res = array();
                                $res['display-name'] = $child->getDisplayName();
                                $res['uri'] = $child->getDataUri();
                                $res['basename'] = basename($res['uri']);
                                array_push($parentResources, $res);
                            
                            } 
                        }
                    }
                }
            
                
            }
             
            
            
            $this->getResourcesRec($id, $childResources); 
            
            bx_helpers_xml::array2Dom(array('parentres' => $parentResources), $content, $content->documentElement); 
            bx_helpers_xml::array2Dom(array('childres' => $childResources), $content, $content->documentElement); 
        
        }
        
        return $content; 
    }
    
   
    private function getResourcesRec($id, &$childs) {
        if ($id) {
        
            $parts = bx_collections::getCollectionAndFileParts($id,"admin");
            if (isset($parts['coll'])) {
                $coll = $parts['coll'];
                $children = $coll->getChildren();
                if (is_array($children) && sizeof($children) > 0) {
                    foreach($children as $child) {
                         
                        switch($child->getMimeType()) {
                        
                            case "httpd/unix-directory":
                                $this->getResourcesRec($child->id, $childs, $level);
                            break;

                            case "text/html":
                                if ($child->getDataUri() != $id) {
                                    $res = array();
                                    $res['display-name'] = $child->getDisplayName();
                                    $res['uri'] = $child->getDataUri();
                                    $res['shorturi'] = str_replace(dirname($id),"", $child->getDataUri());
                                    $res['basename'] = basename($res['uri']);
                                    array_push($childs, $res);
                                }
                                
                            break;
                        
                        } 
                    }
                }
            }
        }
       
        return null;
    }


    

   
    public function handlePOST($path, $id, $data) {
        if ($this->db && (isset($data['name']) || isset($data['parent']))) {
            
            $types = array('text','text','text','text','text','text');
           
            /**
            * Propagate Children
            */
            if (isset($data['child']) && !empty($data['child'])) {
                $this->propagateAssets($id, array($data['child'])); 
                 
            } elseif (isset($data['allchilds'])) {
                
                $children = array();
                $this->getResourcesRec($id, $children);
                if ($children && sizeof($children) > 0) {
                    
                    $childIds = array();
                    foreach($children as $child) {
                        array_push($childIds, $child['uri']);
                    }  
                    
                    $this->propagateAssets($id, $childIds);
                }
                
            } 
           
           
            
           
            $this->db->exec("DELETE FROM ".$this->assetTable." WHERE path='".$id."'");
            
            /**
            * Adobt from Parent of use input
            */
            if (isset($data['parent']) && !empty($data['parent'])) {
                
                $pq = $this->db->query("SELECT * FROM ".$this->assetTable." WHERE path='".$data['parent']."'");
                if (!MDB2::isError($pq)) {
                    
                    $assets = $pq->fetchAll(MDB2_FETCHMODE_ASSOC);
                    if ($assets && sizeof($assets) > 0) {
                        
                                                 
                            $prepqSt = "INSERT INTO ".$this->assetTable." (path,name,value,type,lang,target) VALUES(?,?,?,?,?,?)";
                            $prepq = $this->db->prepare($prepqSt, $types, MDB2_PREPARE_MANIP);
                            
                            foreach($assets as $as) {
                                            
                                $insert = array($id,$as['name'], $as['value'], $as['type'], $as['lang'], $as['target']);
                                $prepres = $prepq->execute($insert);
                            
                            }
                    
                    }
                }
               
            } else {
            
                $prepq = $this->db->prepare("INSERT INTO ".$this->assetTable." (path,name,value,type,lang,target) VALUES(?,?,?,?,?,?)", $types, MDB2_PREPARE_MANIP);
                foreach($data['name'] as $i => $name) {
                    if (!isset($data['delete'][$i]) && !empty($name)) {
                   
                        $insert = array($id, $name, $data['value'][$i], $data['type'][$i], $data['lang'][$i], $data['target'][$i]);
                        $prepres = $prepq->execute($insert);
                    }
                }
            }
        }
        
    }
    
    /*
    * get assets from parentid and propagate to childid
    * the resource-id is equal to the assets table's path column
    */
    private function propagateAssets($parentId, $childIds) {
        if (!is_array($childIds) || sizeof($childIds) == 0) {
            return false; 
        }
        
        $assetsq = "SELECT * FROM ".$this->assetTable." WHERE path='" . $parentId . "'"; 
        $res = $this->db->query($assetsq);
        if (!MDB2::isError($res)) {
            $parentAssets = $res->fetchAll(MDB2_FETCHMODE_ASSOC); 
            if ($parentAssets && sizeof($parentAssets) > 0) {
                
                foreach($childIds as $childId) {
                
                    $delq = "DELETE FROM ".$this->assetTable." WHERE path='".$childId."'";
                    $res = $this->db->exec($delq);
                    if (!MDB2::isError($res)) {
                    
                        $prepq = "INSERT INTO ".$this->assetTable." (path,name,value,type,lang,target) VALUES(?,?,?,?,?,?)"; 
                        $prepqSt = $this->db->prepare($prepq, $this->prepqAssetTypes, MDB2_PREPARE_MANIP);
                        if (!MDB2::isError($prepqSt)) {
                            foreach($parentAssets as $pa) {
                            
                                $insert = array($childId, $pa['name'], $pa['value'], $pa['type'], $pa['lang'], $pa['target']);
                                $prepres = $prepqSt->execute($insert);

                            } 
                        }
                    }
                } 
            }
        }
    }
    
    
    private function getAssetsById($id) {
        $q = "SELECT id,type,name,value,lang,target FROM ".$this->assetTable;
        $q.= " WHERE path='".$id."' ORDER BY id DESC";
        $res = $this->db->query($q);
        
        if (!MDB2::isError($res)) {
            $assets = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
            
            if (is_array($assets) && sizeof($assets) > 0) {
                return $assets;
            }
        }
        
        return false;
    }
    
}




?>
