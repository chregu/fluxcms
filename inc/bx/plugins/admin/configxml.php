<?php

class bx_plugins_admin_configxml extends bx_plugin implements bxIplugin {
    
    protected $res = array();
    
    static public $instance = array();
    static private $idMapper = null;
    
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        
        if (!isset(bx_plugins_admin_configxml::$instance[$mode])) {
            bx_plugins_admin_configxml::$instance[$mode] = new bx_plugins_admin_configxml($mode);
        } 
        return bx_plugins_admin_configxml::$instance[$mode];
    }
    
    protected function __construct($mode) {
         $this->mode = $mode;
    
    }
       
    /**
    * gets the resource object associated to an id
    *
    * this is the preferred method of doing things ;)
    *
    * @param string $id the id of the resource
    * @return object resource
    */
    
    public function getResourceById($path, $id, $mock = false) {
        //FIXME this is dirty...
        $file = BX_DATA_DIR.$path.$id;
        
        if (file_exists($file)) {
            $r =  new bx_resources_file($file,false);
        } else {
            $r =  new bx_resources_file($file,true);
            $r->props['fileuri']  = BX_LIBS_DIR."doctypes/empty.configxml.xml";
        }
       return $r;
       //return new bx_resources_file(BX_LIBS_DIR."doctypes/empty.xml");
    }
    
    public function handlePOST($path, $id, $data, $mode = null) {
    	
       	$perm = bx_permm::getInstance();
		if (!$perm->isAllowed($path, array('collection-back-configxml'))) { 
			throw new BxPageNotAllowedException();
		}
    	
        if ($mode == "FullXML") {
            if (trim($data['fullxml']) == "") { 
                $res = $this->getResourceById($path,$id);
                $res->delete();
            } else {
                $res = $this->getResourceById($path,$id);
                
                if ($res->mock) {
                    $res->create();   
                }
                
                $file = $this->getContentUriById($path,$id); 
                //FIXME: resource should handle the save, not the plugin, actually..
                if (!file_put_contents($file,bx_helpers_string::utf2entities($data['fullxml']))) {
                    print "File $file could not be written\n";
                }
            }
            
        }
        
        
    }

    
    public function isRealResource($path , $id) {
        return false;
    }
    
}
?>
