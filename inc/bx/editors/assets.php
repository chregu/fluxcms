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
        
        }
        
        return $content; 
    }
    
    
    public function handlePOST($path, $id, $data) {
        
        //var_dump($data);
        
        if ($this->db && isset($data['name'])) {
            
            $this->db->exec("DELETE FROM ".$this->assetTable." WHERE path='".$id."'");
            $types = array('text','text','text','text','text','text');
            $prepq = $this->db->prepare("INSERT INTO ".$this->assetTable." (path,name,value,type,lang,target) VALUES(?,?,?,?,?,?)", $types, MDB2_PREPARE_MANIP);
            
            foreach($data['name'] as $i => $name) {
                if (!isset($data['delete'][$i]) && !empty($name)) {
                   
                    $insert = array($id, $name, $data['value'][$i], $data['type'][$i], $data['lang'][$i], $data['target'][$i]);
                    $prepres = $prepq->execute($insert);
                    
                            
                }
            }
            
        }
        
    }
    
    
    private function getAssetsById($id) {
        $q = "SELECT id,type,name,value,lang,target FROM ".$this->assetTable;
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
