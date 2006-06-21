<?php

class bx_plugins_admin_copy extends bx_plugins_admin implements bxIplugin {

    static private $instance = null;
    
    private function __construct() {
    }
    
    public static function getInstance($mode) {
        
        if (self::$instance === NULL) {
            self::$instance = new bx_plugins_admin_copy($mode);
        }
        
        return self::$instance;
    
    }
    
    public function getContentById($path, $id) {
        $to = $this->getParameter($path,"to");
        $move = $this->getParameter($path,"move");
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        $dom = new domDocument();
        $response = $dom->createElement('response');
        $perm = bx_permm::getInstance();
        
        try {
        	$dest = substr($to, 0, strrpos($to, '/', -2)+1);
    		if (!$perm->isAllowed($dest,array('collection-back-create'))) {
	        	throw new BxPageNotAllowedException();
	        }
        	
        	$permId = substr($id, 0, strrpos($id, '/', -1)+1);

            if ($move) {        
		        if (!$perm->isAllowed($permId,array('collection-back-copy', 'collection-back-delete'))) {
		        	throw new BxPageNotAllowedException();
		        }
            	
                $success = $parts['coll']->moveResourceById($parts['rawname'],$to);
                $msg = "$id moved to $to!";
               /* $response->setAttribute("updateTree","$id");
                $response->setAttribute("updateTree2","$to");*/
            } else {
            	if (!$perm->isAllowed($permId,array('collection-back-copy'))) {
		        	throw new BxPageNotAllowedException();
		        }
            	
                $success = $parts['coll']->copyResourceById($parts['rawname'],$to);
                $msg = "$id copied to $to!";
                /*$response->setAttribute("updateTree","$id");
                $response->setAttribute("updateTree2","$to");*/
            }
            
			// copy permissions (without inherits) if we copied a directory
			if(substr($id, -1) == '/')
			{
				$prefix = $GLOBALS['POOL']->config->getTablePrefix();
				
	            $query = "	
	            INSERT {$prefix}perms( 
				`fk_group` , 
				`plugin` , 
				`action` , 
				`uri` , 
				`inherit` 
				) 
				SELECT {$prefix}perms.fk_group, {$prefix}perms.plugin, {$prefix}perms.action, REPLACE( {$prefix}perms.uri, '{$permId}', '{$to}' ) , {$prefix}perms.inherit 
				FROM {$prefix}perms 
				WHERE LOCATE( '{$permId}', {$prefix}perms.uri ) !=0 
				AND {$prefix}perms.inherit = '';";
	
				$GLOBALS['POOL']->dbwrite->exec($query); 
	            
	                  
	            if ($move) {
		            $query = "	DELETE FROM {$prefix}perms 
								WHERE LOCATE('{$permId}', {$prefix}perms.uri) != 0";
	
					$GLOBALS['POOL']->dbwrite->exec($query); 
	            }
			}
            
        } catch (PopoonFileNotFoundException $e) {
            $success = false;
            $msg = $e->getMessage() ." Please use a correct path.";
        }
        
        
        if ($success) {
            $response->setAttribute("status","ok");
            $response->appendChild($dom->createTextNode($msg));
        } else {
            $response->setAttribute("status","failed");
            if ($msg) {
                $response->appendChild($dom->createTextNode($msg));
            } else {
                $response->appendChild($dom->createTextNode('failed'));
            }
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
