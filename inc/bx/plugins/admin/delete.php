<?php

class bx_plugins_admin_delete extends bx_plugins_admin implements bxIplugin {

    static private $instance = null;
    
    private function __construct() {
    }
    
    public static function getInstance($mode) {
        
        if (self::$instance === NULL) {
            self::$instance = new bx_plugins_admin_delete($mode);
        }
        
        return self::$instance;
    
    }
    
    public function getContentById($path, $id) {
        
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        
        $permId = substr($id, 0, strrpos($id, '/', -1)+1);
        
        $perm = bx_permm::getInstance();
        if (!$perm->isAllowed($permId,array('collection-back-delete'))) {
        	throw new BxPageNotAllowedException();
        }

        $dom = new domDocument();
        $response = $dom->createElement('response');
        if ($parts['coll']->deleteResourceById($parts['rawname'])) {
            $response->appendChild($dom->createTextNode('ok'));
            
            if(substr($id, -1) == '/') {
	            // delete permissions
	            $prefix = $GLOBALS['POOL']->config->getTablePrefix();
	            $query = "	DELETE FROM {$prefix}perms 
							WHERE LOCATE('{$permId}', {$prefix}perms.uri) != 0";
		
				$GLOBALS['POOL']->dbwrite->exec($query); 
            }

        } else {
            $response->appendChild($dom->createTextNode('failed'));
        }
        
        
        $dom->appendChild($response);
        return $dom;
        
    }
    
    
    public function getResourceById($path,$id) {
        return false;
    }
    
    
    public function getIdByRequest($path, $name=NULL, $ext=NULL) {
        return "/$name.$ext";
    }
    
    
    public function getContentUriById($path, $id, $sample = false) {
        
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        return $parts['coll']->getContentUriById($parts['rawname'],$sample);   
         
    }
}


?>
