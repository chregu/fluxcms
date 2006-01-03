<?php

class bx_propertyconfig {

    protected $configXML = NULL;
    protected $properties = array();

    protected $propertiesByID = array();
    protected $propertiesByCategory = array();
    protected $propertiesBySet = array();
    
    /**
    * class constructor
    *
    * @param name type description 
    * @return object 
    */
    public function __construct($configFile) {
        if($this->loadConfigXML($configFile)) {
            $this->loadProperties();
        }
    }

    /**
    * loads the given config file as a DOM document nad assigns it to the
    * configXML property.
    *
    * @param configFile string Name of the config file to load 
    * @return TRUE on success, FALSE otherwise 
    */
    private function loadConfigXML($configFile) {
        $this->configXML = new DomDocument();
         $this->configXML->load($configFile);
        $this->configXML->xinclude();
        return true;
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    protected function loadProperties() {
        foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:properties/bxcms:property") as $node) {
            $property = array(
                'deleteOnEmpty' => FALSE
            );

            $property['name'] = $node->getAttribute('name');
            $property['namespace'] = $node->getAttribute('ns');
            if($node->getAttribute('multilang') != '') {
                $property['multilang'] = TRUE;
            }
            if($node->getAttribute('deleteOnEmpty') != '') {
                $property['deleteOnEmpty'] = TRUE;
            }
            
              if($node->hasAttribute('niceName') ) {
                $property['niceName'] = $node->getAttribute('niceName');
            }

            foreach($this->getXPathNodes("bxcms:metadata", $node) as $mdNode) {
                $property['metadata'] = $mdNode->getAttribute('type');
            }

            foreach($this->getXPathNodes("bxcms:validator", $node) as $vNode) {
                $property['validator'] = $vNode->getAttribute('type');
            }
            
            $this->properties[$node->getAttribute('id')] = $property;
            
        }
        //bx_helpers_debug::webdump($this->properties);
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    protected function getPropertiesByID($id) {
        if(!empty($this->propertiesByID[$id])) {
            return $this->propertiesByID[$id];
        }

        $this->propertiesByID[$id] = array();

        if(!empty($this->properties[$id])) {
            $property = $this->properties[$id];
            if(!empty($property['multilang'])) {
                foreach($GLOBALS['POOL']->config->getOutputLanguages() as $lang) {
                    $property = $this->properties[$id];
                    $property['namespace'] = $property['namespace'].$lang;
                    $this->propertiesByID[$id][$property['namespace'].':'.$property['name']] = $property;
                }
            } else {
                $this->propertiesByID[$id][$property['namespace'].':'.$property['name']] = $property;
            }
        }
        return $this->propertiesByID[$id];
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    protected function getDefaultPropertySet() {
        $defaultSetNS = $this->getXPathNodes("/bxcms:bxcms/bxcms:propertysets[@default]");
        if($defaultSetNS->length > 0) {
            return $defaultSetNS->item(0)->getAttribute('default');
        }
        return FALSE;
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getDefaultCategory() {
        $defaultCatNS = $this->getXPathNodes("/bxcms:bxcms/bxcms:categories[@default]");
        if($defaultCatNS->length > 0) {
            return $defaultCatNS->item(0)->getAttribute('default');
        }
        return FALSE;
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getPropertiesByCategory($category) {
        if(!empty($this->propertiesByCategory[$category])) {
            return $this->propertiesByCategory[$category];
        }

        $this->propertiesByCategory[$category] = array();
        foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:categories/bxcms:category[@name='$category']/bxcms:property") as $propertyNode) {
            $this->propertiesByCategory[$category] = array_merge($this->propertiesByCategory[$category], $this->getPropertiesByID($propertyNode->getAttribute('id')));
        }
        return $this->propertiesByCategory[$category];
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getPropertiesByResourceName($resourceName) {
        if(!empty($this->propertiesBySet[$resourceName])) {
            return $this->propertiesBySet[$resourceName];
        }
        
        $this->propertiesBySet[$resourceName] = array();
        foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:propertysets/bxcms:propertyset/bxcms:resource[@name='$resourceName']") as $resourceNode) {
            foreach($this->getXPathNodes("bxcms:property", $resourceNode->parentNode) as $propertyNode) {
                $this->propertiesBySet[$resourceName] = array_merge($this->propertiesBySet[$resourceName], $this->getPropertiesByID($propertyNode->getAttribute('id')));
            }
        }
         // return the parent propertyset if there is no property set for this type of resource
         if(empty($this->propertiesBySet[$resourceName])) {
             foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:propertysets/bxcms:propertyset/bxcms:resource[@name='".dirname($resourceName)."']") as $resourceNode) {
                 foreach($this->getXPathNodes("bxcms:property", $resourceNode->parentNode) as $propertyNode) {
                     $this->propertiesBySet[$resourceName] = array_merge($this->propertiesBySet[$resourceName], $this->getPropertiesByID($propertyNode->getAttribute('id')));
                 }
             }
         }
        
        // return the default propertyset if there is no property set for this type of resource
        if(empty($this->propertiesBySet[$resourceName])) {
            foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:propertysets/bxcms:propertyset[@name='".$this->getDefaultPropertySet()."']/bxcms:property") as $propertyNode) {
                $this->propertiesBySet[$resourceName] = array_merge($this->propertiesBySet[$resourceName], $this->getPropertiesByID($propertyNode->getAttribute('id')));
            }
        }
        return $this->propertiesBySet[$resourceName];
    }
    
    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getCategories() {
        if(!empty($this->categories)) {
            return $this->categories;
        }

        foreach($this->getXPathNodes("/bxcms:bxcms/bxcms:categories/bxcms:category") as $categoryNode) {
            $name = $categoryNode->getAttribute('name');
            $this->categories[$name] = array();
            if(sizeof($this->getPropertiesByCategory($name)) > 0) {
                $this->categories[$name]['hasProperties'] = TRUE;
            }
        }

        return $this->categories; 
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getCategoriesByResourceName($resource) {
        //var_dump($resource);
        if(!empty($this->categoriesByResourceName)) {
            return $this->categoriesByResourceName;
        }

        foreach(array_keys($this->getCategories()) as $category) {
            $this->categoriesByResourceName[$category] = array();
            if(sizeof($categoryProperties = $this->getPropertiesByCategoryAndResourceName($category, $resource)) > 0) {
                $this->categoriesByResourceName[$category]['hasProperties'] = TRUE;
            }
        }

        return $this->categoriesByResourceName; 
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getPropertiesByCategoryAndResourceName($category, $resource) {
        $properties = array();
        $categoryProperties = $this->getPropertiesByCategory($category);
        $resourceProperties = $this->getPropertiesByResourceName($resource);
        foreach($categoryProperties as $fullName => $property) {
            if(in_array($property, $resourceProperties)) {
                $properties[$fullName] = $property;
            }
        }
        return $properties;
    }
    
    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    public function getMetadatasByCategoryAndResourceName($category, $resource) {
        $metadatas = array();
        foreach($this->getPropertiesByCategoryAndResourceName($category, $resource) as $fullName => $property) {
            $metadatas[$fullName] = $this->getMetadataInstance($property['metadata']);
        }
        return $metadatas;
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    protected function getMetadataInstance($type) {
        if ($type) {
            $className = "bx_metadatas_".$type;
            return new $className;
        } else {
            $className = "bx_metadatas_text_textfield";
            return new $className;
        
            
        }
    }

    /**
    * ...
    *
    * @param name type description 
    * @return object 
    */
    protected function getXPathNodes($xpath, $ctxt = NULL) {
        $xp = new Domxpath($this->configXML);
        $xp->registerNamespace("bxcms","http://bitflux.org/propertyconfig");
        if ($ctxt) {
            return  $xp->query($xpath, $ctxt);    
        } else {
            return  $xp->query($xpath);
        }
    }
    
}

?>
