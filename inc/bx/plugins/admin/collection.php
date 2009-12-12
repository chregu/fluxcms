<?php

class bx_plugins_admin_collection extends bx_plugin {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_collection::$instance) {
            bx_plugins_admin_collection::$instance = new bx_plugins_admin_collection($mode);
        } 
        
        return bx_plugins_admin_collection::$instance;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if ($ext) {
            return $name.".$ext";    
        } else if ($name == '') {
            return '/';
        } else {
            return $name;
        }
    }

    protected function getAddResourceParams($path) {
    	
        $dom = new domDocument();
    
        $fields = $dom->createElement('fields');

        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'name');
        $nameNode->setAttribute('type', 'text');
        
        $templNode = $dom->createElement('field');
        $templNode->setAttribute('name', 'resource');
        $templNode->setAttribute('type', 'select');

        $mainColl = bx_collections::getCollection($path);
        $resourceTypes = $mainColl->getPluginResourceTypes();
        $resourceTypes[] = 'none';
        foreach($resourceTypes as $resourceType) {
            $resOpt = $dom->createElement('option');
            $resOpt->setAttribute('name', $resourceType);
            $resOpt->setAttribute('value', $resourceType);
            $templNode->appendChild($resOpt);
        }

        $fields->appendChild($templNode);
        $fields->appendChild($nameNode);
        $dom->appendChild($fields);
        
        return $dom;
    }
    
    
    public function getContentById($path, $id) {
        return $this->getAddResourceParams($id);
    }

    public function handlePost($path, $id, $data) {
    	
    	$perm = bx_permm::getInstance();
    	$permId = (strlen($id) > 1 ? '/'.$id : $id);
		if (!$perm->isAllowed($permId ,array('collection-back-create'))) {
        	throw new BxPageNotAllowedException();
    	}
    	
        if(!empty($data['name']) && !empty($data['resource'])) {
            $id = '/'.$id;
            $name = bx_helpers_string::makeUri($data['name']);
            if($this->makeCollection($id.$name,$data['name'])) {
                if($data['resource'] != 'none') {
                    $location = BX_WEBROOT.'admin/addresource'.$id.$name.'/?type='.$data['resource'].'&name=index&updateTree='.$id;
                } else {
                    $location = BX_WEBROOT.'admin/properties'.$id.$name.'/?updateTree='.$id;
                }
                
                header("Location: $location");
            }
        }
    }

    protected function getParentCollection($path) {
        $parent = dirname($path);
        if ($parent != "/"){
            $parent .= '/';
        }
        return bx_collections::getCollection($parent,"output");
    }

    /**
    *  creates a new collection
    *
    * @param string $path path to the parent collection
    * @param string $name name of the new collection
    * @return mixed new collection on success, FALSE otherwise
    */
    public function makeCollection ($path, $name = '') {
        $maincoll = bx_collections::getCollection($path);
        $masterPlugin = $maincoll->getAdminMasterPlugin();
        
        // if $masterPlugin has it's own makeCollection, call that...
        if (method_exists($masterPlugin,"makeCollection")) {
            return $masterPlugin->makeCollection($maincoll->uri,preg_replace("#^".$maincoll->uri."#","",$path));
        }

        // create new collection
        $coll = new bx_collection($path."/","output", true);
        if($coll instanceof bx_collection) {
            $parentColl = $this->getParentCollection("$path");
            if($parentColl instanceof bx_collection) {
                // inherite pipeline and default properties from parent collection 
                $props = $parentColl->getAllProperties(BX_PROPERTY_PIPELINE_NAMESPACE);
                foreach($props as $prop) {
                    $coll->setProperty($prop['name'], $prop['value'], $prop['namespace']);
                }
                if ($name != '') {
                    $coll->setProperty("display-name",$name, BX_PROPERTY_DEFAULT_NAMESPACE.BX_DEFAULT_LANGUAGE);
                }
                return $coll;       
            }
        }
        return FALSE;
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
		
		/** bx_plugin::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
				$params = array();
				$params['xslt'] = 'collection.xsl';
				return $params;
		}

    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
    
}
?>
