<?php

class bx_plugins_admin_properties extends bx_component implements bxIplugin {
    
   static private $instance = array();
   private $propConfig = NULL;
   private $lastConfigFile = NULL;
   private $category = '';
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_plugins_admin_properties($mode);
        } 
        return self::$instance;
    }   
    
    protected function __construct($mode) {
        $this->mode = $mode;
        //$this->loadConfigFile();
        // create instance of property config
       

    }
    protected function loadConfigFile($resourceName = null) {
        $configFile = null;
        if ($resourceName ) {
            $configFile = BX_PROJECT_DIR.'conf/properties/'.str_replace("_","/",$resourceName).'.xml';
            if (!file_exists($configFile)) {
                $configFile = BX_PROJECT_DIR.'conf/properties/'.dirname($resourceName). '.xml';
                if (!file_exists($configFile)) {
                    $configFile = null;
                }
                
            }
        }
        if (!$configFile) {
            if (!(defined('BX_PROPERTYCONFIG_FILENAME'))) {
                //define('BX_PROPERTYCONFIG_FILENAME',BX_PROJECT_DIR.'conf/properties/audio/mpeg.xml');
                define('BX_PROPERTYCONFIG_FILENAME',BX_PROJECT_DIR.'conf/properties/properties.xml');
            }
            $configFile = BX_PROPERTYCONFIG_FILENAME;
        }
        
        if ($this->lastConfigFile != $configFile) {
            $this->propConfig = new bx_propertyconfig($configFile);
            $this->lastConfigFile = $configFile;
        }

        // get category from request var
        $categories = $this->propConfig->getCategories();
        $this->category = isset($_REQUEST['category']) ? $_REQUEST['category'] : NULL;
        if(!in_array($this->category, array_keys($categories))) {
            $this->category = $this->propConfig->getDefaultCategory();
        }
    }
    
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        if ($ext) {
            return $name.".$ext";    
        } else {
            return $name;
        }
    }

    protected function getFullPath($name, $ext) {
        if(!empty($ext)) {
            return "/$name.$ext";
        } else {
            return "/$name";
        }
    }
    
    public function getResourceById($path, $id, $mock = false) {
        print $id;
    }
    
    public function getContentUriById($path, $id) {
    }
    
    public function getContentById($path, $id) {

        $fullPath = $id;
        $dom = new domDocument();
        $dom->appendChild($dom->createElement('properties'));
        
        $dom->documentElement->setAttribute('path', $fullPath);
        if (isset($_GET['updateTree']) ) {
            $dom->documentElement->setAttribute('updateTree',$_GET['updateTree']);
        }
        
        $parts  = bx_collections::getCollectionAndFileParts($fullPath, $this->mode);
        $coll = $parts['coll'];
        $id = $parts['rawname'];
        
        if($coll instanceof bx_collection) {
            if ($id == "") {
                $res = $coll;
            } else {
                $plugin = $coll->getPluginById($id);
                if (!$plugin) {
                    $res =   new bx_resources_file($path,$id);
                } else {
                    $res = $plugin->getResourceById($coll->uri, $id);
                }
            }

        // create node containing all categories
        $this->loadConfigFile($res->getMimeType());
        $categories = $this->propConfig->getCategoriesByResourceName($res->getMimeType());
        $catNode = $dom->createElement('categories');
        foreach($categories as $category => $cat) {
            $node = $dom->createElement('category');
            $node->setAttribute('name', $category);
            if(!empty($cat['hasProperties'])) {
                $node->setAttribute('hasProperties', 'true');
            }
            // mark active category
            if($category == $this->category) {
                $node->setAttribute('active', 'true');
            }
            $catNode->appendChild($node);
        }
        $dom->documentElement->appendChild($catNode);

          
            if($res instanceof bxIresource) {
                
                // get all properties for this resource
                $resourceProperties = $res->getAllProperties();

                // get all properties for the current category and resource name
                $categoryProperties = $this->propConfig->getPropertiesByCategoryAndResourceName($this->category, $res->getMimeType());
                $categoryMetadatas = $this->propConfig->getMetadatasByCategoryAndResourceName($this->category, $res->getMimeType());

                if(!empty($categoryProperties)) {
                    foreach($categoryProperties as $key => $property) {

                        $propNode = $dom->createElement('property');
                        $propNode->setAttribute('path', $fullPath);
                        $propNode->setAttribute('namespace', $property['namespace']);
                        $propNode->setAttribute('name', $property['name']);
                        if (isset($property['niceName'])) {
                            $propNode->setAttribute('niceName', $property['niceName']);
                        }
                        
                        // use an already existing value or get a default one from the metadata or the property config
                        if(isset($resourceProperties[$key]['value'])) {
                            $value = $resourceProperties[$key]['value'];
                        } else if(method_exists($categoryMetadatas[$key], 'getDefaultValue')) {
                            $value = $categoryMetadatas[$key]->getDefaultValue();
                        } else if(!empty($categoryProperties[$key]['defaultValue'])) {
                            $value = $categoryProperties[$key]['defaultValue'];
                        } else {
                            $value = NULL;
                        }
                        
                        $propNode->setAttribute('value', html_entity_decode($value, ENT_NOQUOTES, "UTF-8"));
                        
                        $propNode->setAttribute('fieldname', $key);
        
                        if($categoryMetadatas[$key] instanceof bx_metadata) {
                            $import = $dom->importNode($categoryMetadatas[$key]->serializeToDOM(), TRUE);
                            $propNode->appendChild($import);
                        }
        
                        $dom->documentElement->appendChild($propNode);
                    }
                }
            }
        }
        
        return $dom;
    }
    
    public function handlePOST($path, $id, $data) {
        // rewrite name and extension from extracted fullpath
        $fullPath = str_replace("//","/","/".$id);
        
        $parts  = bx_collections::getCollectionAndFileParts($fullPath, $this->mode);
        $coll = $parts['coll'];
        $id = $parts['rawname'];
        
        if(!empty($data[$fullPath])) {
            if ($id == "")  {
               $res = $coll;
            } else {
               
               $plugin = $coll->getPluginById($id);
               if ($plugin instanceof bxIplugin) {
                   $res = $plugin->getResourceById($coll->uri,$id);
               } else {
                   //FIXME: show better alerts...
                   print "No matching plugin found ... Can't save properties";
                   return false;
               }
            }
       
            // get all properties for this resource
            $resourceProperties = $res->getAllProperties();
            $this->loadConfigFile($res->getMimeType());
            // get all properties for the current category and resource name
            $categoryProperties = $this->propConfig->getPropertiesByCategoryAndResourceName($this->category, $res->getMimeType());
            $categoryMetadatas = $this->propConfig->getMetadatasByCategoryAndResourceName($this->category, $res->getMimeType());

            if(!empty($categoryProperties)) {
                foreach($categoryProperties as $key => $property) {
                    
                    if($categoryMetadatas[$key]->isChangeable()) {
                    
                        $value = isset($data[$fullPath][$key]) ? $data[$fullPath][$key] : NULL;
                        
                        // let metadata class handle the posted value if possible
                        if(method_exists($categoryMetadatas[$key], 'getPropertyValueFromPOSTValue')) {
                            $value = $categoryMetadatas[$key]->getPropertyValueFromPOSTValue($value,$res,$property['name']); 
                        }
                        
                        // look for changed or new values and update them                        
                        if((isset($resourceProperties[$key]) && ($value !== $resourceProperties[$key]['value'])) || (!isset($resourceProperties[$key]) && !(empty($value) && $value !== "0"))) {
                            bx_log::log("set property $key = $value for $fullPath");
                            $res->setProperty($property['name'], bx_helpers_string::utf2entities($value), $property['namespace']);
                            
                        }
                        
                        // delete empty properties if required                        
                        // but not if $value === 0
                        if(empty($value) && $property['deleteOnEmpty'] && isset($resourceProperties[$key]) && $value !== "0") {
                            bx_log::log("removed property $key for $fullPath");
                            $res->removeProperty($property['name'], $property['namespace']);
                        }
                    }
                }
            }
            
            // we obvisously have handled some POST data
            return TRUE;            
        }

        return FALSE;
    }

    public function getContentUri($path,$name, $ext) {
        return FALSE;
    }

    public function getResourceByRequest($path, $name, $ext) {
        return FALSE;
    }

		/** bx_plugin::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
				return array();
		}
    
    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getChildren($uri) {
      return FALSE;
    }
    
    public function getEditorsByRequest($path, $name, $ext) { 
        return FALSE;
    }
    public function getDataUri() {
        return NULL;
    }
    
    public function getStylesheetNameById() {
    }
    
    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
    
    public function stripRoot() {
        return false;
    }
      
}
?>
