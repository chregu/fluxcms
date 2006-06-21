<?php

class bx_editors_assets extends bx_editor implements bxIeditor {
    
    private $assetTable = 'assets';
    
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
        }
        
        $assets = $this->getAssetsById($id);
        if (is_array($assets)) {
            bx_helpers_xml::array2Dom($assets, $content, $content->documentElement);
        }
        
        return $content; 
    }
    
    
    private function getAssetsById($id) {
        $q = "SELECT id,type,name,value FROM ".$this->assetTable;
        $q.= " WHERE path='".$id."'";
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
