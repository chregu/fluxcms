<?php

class bx_plugins_assets extends bx_plugin implements bxIplugin {
    
    protected static $instance = null;
    protected $db = null;
    protected $assetTable = 'assets';
    protected $prefx = '';
    protected $lang = '';
    
    protected function __construct($mode) {
        $this->db = $GLOBALS['POOL']->db; 
        $this->prefx = $GLOBALS['POOL']->config->getTablePrefix();
        $this->assetTable = $this->prefx.$this->assetTable;
        $this->lang = $GLOBALS['POOL']->config->getOutputLanguage();
    }

    public function getInstance($mode) {
        if (!self::$instance instanceof bx_plugins_assets) {
            self::$instance = new bx_plugins_assets($mode);
        }    
        
        return self::$instance;
    }
    
    public function getEditorsById($path, $id) {
        return array("assets");
    }   
    
    public function getContentById($path, $id) {
        $idparts = explode(".", $id);
        $resourceId = $idparts[0].".".$this->lang.".xhtml";    
        $resourcep = $path.$resourceId;
        $query = 'SELECT * FROM '.$this->assetTable.' WHERE path="'.$resourcep.'"';
        
        $dom = new DOMDocument();
       
        
        if ($this->db) {
            $res = $this->db->query($query);
            if (!MDB2::isError($res)) {
                $assets = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
                if ($assets && is_array($assets)) {
                      
                    $dom->loadXML('<assets/>');
                    bx_helpers_xml::array2Dom($assets, $dom, $dom->documentElement);
                }
                
            } else {
                var_dump($res->getUserinfo());
            }
        }

        return $dom;
        
    }
    



}

?>
