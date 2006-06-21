<?php

abstract class bx_resource implements bxIresource {
    
    protected $id;
    protected $mimetype;
    
    protected $properties = array();
    protected $allproperties = array();
    protected $lang = null;
    public $mock = false;
    
    public function __construct() {
    }
    
    
    public function getContentHandle() {
        return fopen($this->getContentUri());
    }
    
    public function setProperty($name, $value, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $fullname = $namespace .":".$name;
        bx_resourcemanager::setProperty($this->id, $name, $value, $namespace);
        $this->properties[$fullname] = $value;
        $this->allproperties = array();
    }
    
    public function getAllProperties($namespace = null) {
       
        if (!isset($this->allproperties[$namespace])) {
            $this->allproperties[$namespace] =  bx_resourcemanager::getAllProperties($this->id, $namespace);
            
            foreach ($this->allproperties[$namespace]  as $key => $value) {
                $this->properties[$key] = $value['value'];
            }
        }
        return $this->allproperties[$namespace];
    }
    
    public function getProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        if ($namespace == BX_PROPERTY_DEFAULT_NAMESPACE) {
            switch ($name) {
               case "mimetype":
                    return $this->getMimeType();
               case "output-mimetype":
                    return $this->getOutputMimeType();
               case "id":
                    return $this->getId();
               case "localname":
                    return $this->getLocalName();
               case "creationdate":
                    return $this->getCreationDate();
               case "datauri";
                    return $this->getDataUri();
               case "displayname";
                    return $this->getDisplayname();
               case "lastmodified";
                    return $this->lastModified();
            }
        }
        $fullname = $namespace .":".$name;
        if (!isset($this->properties[$fullname])) {
           $this->getAllProperties($namespace);
        }
        if (!isset($this->properties[$fullname])) {
            return NULL;
        }
        return $this->properties[$fullname];
    }

    public function removeProperty($name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        bx_resourcemanager::removeProperty($this->uri, $name, $namespace);
    }
        
    public function saveFile($file , $uploadInfo = NULL) {
        // overwrite old file with uploaded file
        $fspath = $this->getContentUri();
        if (!$fspath) {
            return false;
        }
        
        if ($this->mock) {
            $this->init();
        }
        if(!empty($fspath) && file_exists($file)) {
            if ( copy($file, $fspath)) {
                $mt = popoon_helpers_mimetypes::getFromFileLocation($fspath);
                if ($mt) {
                    $this->callMetaIndex($fspath);
                }
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    
    public function callMetaIndex($fspath) {
        bx_metaindex::callIndexerFromFilename($fspath,$this->id);
    }
    
    public function save () {
        if ($this->mock) {
           return $this->saveFile($this->getContentUriSample());
        }
                 
        print "TO BE IMPLEMENTED " . __FILE__ . " " . __METHOD__;
        
    }
    
    public function getContentUri($sample = false) {
        return NULL;
    }
    
    public function getContentUriSample() {
        return NULL;
        
        //return BX_LIBS_DIR.'doctypes/dreispalt.xhtml';
    }

    public function getId() {
        return $this->id;
    }
    
    public function getLocalName() {
        if (isset($this->props['localname'])) {
            return $this->props['localname'];
        }
        if (isset($this->props['fileuri'])) {
            $b = basename($this->props['fileuri']);
            $p = bx_collections::getFileParts($b);
            
            $this->props['localname'] = $p['name'].".".$p['ext'];
            return $this->props['localname'];
        }
        return null;
    }
    
    public function getLanguage() {
        if ($this->lang) {
            return $this->lang;
        } else {
            return $GLOBALS['POOL']->config->getOutputLanguage();
        }
    }
    
      public function getOutputUri() {
        if ($this->props['outputUri']) {
            return $this->props['outputUri'];
        } else {
           return $this->getDataUri();
        }
    }
    
    public function getDisplayName() {
        if (isset($this->props['displayname'])) {
            return $this->props['displayname'];
        }
        if (isset($this->props['fileuri'])) {
            $b = basename($this->props['fileuri']);
            $p = bx_collections::getFileParts($b);
            
            $this->props['displayname'] = $p['name'];
            return $this->props['displayname'];
        }
        return null;
    }
    
    public function getTitle() {
      if (isset($this->props['title'])) {
          return $this->props['title'];
      } else {
          return $this->getDisplayName();
      }
        
    }
    
     public function getStatus() {
      if (isset($this->props['status'])) {
          return $this->props['status'];
      } else {
          return 1;
      }
        
    }
    
    public function getDisplayOrder() {
    }
   	
    public function getDisplayImage() {
    }

    public function getMimeType() {
        if (isset($this->props['mimetype'])) {
            return $this->props['mimetype'];
        } else {
            return $this->getResourceName();
        }
        return null;
    }
    
    public function getOutputMimeType() {
         if (isset($this->props['output-mimetype'])) {
            return $this->props['output-mimetype'];
        }
        if (isset($this->props['mimetype'])) {
            return $this->props['mimetype'];
        }
        return null;
    }
    
    public function getLastModified() {
        if (isset($this->props['lastmodified'])) {
            return $this->props['lastmodified'];
        }
        if (isset($this->props['fileuri'])) {
            $this->props['lastmodified'] = @filemtime($this->props['fileuri']);
            return $this->props['lastmodified'];
        }
        return null;
    }
    
    public function getCreationDate() {
        if (isset($this->props['creationdate'])) {
            return $this->props['creationdate'];
        }
        if (isset($this->props['fileuri'])) {
            $this->props['creationdate'] = @filectime($this->props['fileuri']);
            return $this->props['creationdate'];
        }
        return null;
    }
    
    public function getContentLength() {
        if (isset($this->props['contentlength'])) {
            return $this->props['contentlength'];
        }
        if (isset($this->props['fileuri'])) {
            $this->props['contentlength'] = @filesize($this->props['fileuri']);
            return $this->props['contentlength'];
        }
        return null;
    }
   
    public function getEditors() {
        return array();   
    }

    public function getDataUri() {
        return $this->fulluri;
    }
    
    public function getResourceName() {
        return get_class($this);
    }
    
    public function getResourceDescription() {
        if (isset($this->props['resourceDescription'])) {
            return $this->props['resourceDescription'];
        }
    }
    
    public function onSave() {
        return null;
    } 
    
    public function getOverviewSections($dom,$coll = null) {
        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));

        if($permObj->isAllowed('/',array('admin'))) {
        	
	        $perm = bx_permm::getInstance();
	       	$permId = substr($this->id, 0, strrpos($this->id, '/', -1)+1);
	
	        //$dom->setIcon("resource");
	         $dom->setType("resource");
	         $dom->setPath($this->id);
	         
	         $dom->addSeperator();
	         if ($perm->isAllowed($permId, array('collection-back-properties'))) {
	         	$dom->addLink("Properties", "properties".$this->id);
	         }
	             if ($coll && $this->getMimetype() == 'httpd/unix-directory') {
	                 $dom->addSeperator();
	                 $dom->addLink("Create new Collection", 'collection'.$this->id);
	                 $resourceTypes = $coll->getPluginResourceTypes();
	                 if(!empty($resourceTypes)) {
	                     foreach($resourceTypes as $resourceType) {
	                         $dom->addLink("Create new " . $resourceType,'addresource'. $this->id.'?type='.$resourceType);
	                     }
	                 }
	             }
	             
	           
	             $dom->addTab("Operations");
	             if ($perm->isAllowed($permId,array('collection-back-copy'))) {
	             	$dom->addLink("Copy",'javascript:parent.navi.admin.copyResource("'.$this->id.'");');
	             }
	             if ($perm->isAllowed($permId,array('collection-back-copy', 'collection-back-delete'))) {
	             	$dom->addLink("Move/Rename",'javascript:parent.navi.admin.copyResource("'.$this->id.'",true);');
	             }
	             if ($perm->isAllowed($permId,array('collection-back-delete'))) {
	             	$dom->addLink("Delete",'javascript:parent.navi.admin.deleteResource("'.$this->id.'");');
	             }
         }
    }
}

?>
