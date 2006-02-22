<?php

class bx_collectionconfig {

    protected $configxml = null;
    protected $mode;
    protected $parameters = array();
    protected $variables = array();

    public function __construct($uri, $mode) {
        $this->uri = $uri;
        $this->mode = $mode;
        $this->loadConfigXML($this->uri);
    }

    private function loadConfigXML($url) {
        $this->configxml = new DomDocument();
        $this->configxml->load("bxconfig://".$url);
        $xp = new domxpath($this->configxml);
        $xp->registerNamespace("xi","http://www.w3.org/2001/XInclude");
        $xp->registerNamespace("bxcms","http://bitflux.org/config");
        
        $res = $xp->query("//xi:include");
        foreach( $res as $node) {
            $href = $node->getAttribute("href");
            if (substr($href,0,3) == "BX_") {
            	if (BX_OS_WIN) {
               	 	$href = preg_replace("#(BX_[A-Z_]+)://#e","'file:///'.constant('$1')",$href);
                } else {
                 	$href = preg_replace("#(BX_[A-Z_]+)://#e","'file://'.constant('$1')",$href);
                }
	            $node->setAttribute('href',$href);
            }
        }
        $this->configxml->xinclude();
        //parse variables
        $res = $xp->query("/bxcms:bxcms/bxcms:variable");
        foreach( $res as $node) {
            $name = $node->getAttribute("name");
            if (!isset($this->variables[$name])) {
                $this->variables[$name] = $node->getAttribute("value");
            }
        }
        if (count($this->variables) == 0) {
            $this->variables = null;
        }
    }
    
    public function getPlugins($name, $ext, $first = false) {
        $ext = strtolower($ext);
        $plugins = array();
        if ($this->mode == "admin") {
            $xpathAddition = "";//"[@admin='true']";
        } else {
            $xpathAddition = "";
        }
        $hit = false;
        foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:plugins/bxcms:extension") as $node) {
            if ($ext == $node->getAttribute("type")) {
                $res = $this->getXPathNodes("bxcms:file", $node->parentNode);
                if($res->length > 0) {
                    // filter matches only specific file names
                    foreach($res as $fileNode) {

                        if ( $name == $fileNode->getAttribute('name') ||
                            ($fileNode->getAttribute("preg") && preg_match($fileNode->getAttribute("preg"),$name))) {
                            $res = $this->getXPathNodes("bxcms:plugin".$xpathAddition,$node->parentNode);
                            foreach ($res as $p) {
                                $type = $p->getAttribute("type");
                                $output = $this->getPluginInstance($type, $this->mode);
                                $params = $this->getAllNodeParameters($p);
                                $output->setParameterAll($this->uri, $params);
                                if ($first) {
                                    return $output;
                                }
                                $plugins[$type] = $output;
                                $hit = true;
                            }
                            // parse collection parameters
                            $this->parameters = $this->getAllNodeParameters($node->parentNode);
                        }
                    }
                } else {
                    $res = $this->getXPathNodes("bxcms:plugin".$xpathAddition,$node->parentNode);
                    foreach ($res as $p) {
                        $type = $p->getAttribute("type");
                        $output = $this->getPluginInstance($type, $this->mode);
                        $params = $this->getAllNodeParameters($p);
                        $output->setParameterAll($this->uri, $params);
                        if ($first) {
                            return $output;
                        }
                        $plugins[$type] = $output;
                        $hit = true;
                    }
                    // parse collection parameters
                    $this->parameters = $this->getAllNodeParameters($node->parentNode);
                }
                if ($hit) {break;}
            }
        }

        //if no match, get default plugin (the one with no bxcms:extensions ;) )
        if (!($plugins)) {
            foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:plugins[not(bxcms:extension)]/bxcms:plugin".$xpathAddition) as $p) {
                $type = $p->getAttribute("type");
                $output = $this->getPluginInstance($type,$this->mode);

                $params = $this->getAllNodeParameters($p);
                $output->setParameterAll($this->uri,$params);
                if ($first) {
                    return $output;
                }
                $plugins[$type] = $output;
                $node = $p;
            }
            $this->parameters = $this->getAllNodeParameters($node->parentNode);
        }

        //return collection plugin if extension is 'collection' and default didn't match
        if($ext === 'collection' && empty($plugins)) {
            return $this->getPluginInstance('collection', $this->mode);
        }


        if ($first ) {
            return NULL;
        }

        return $plugins;
    }


    public function getFirstPlugin ($name, $ext) {
        print "deprecated!\n";
        return $this->getPlugins($name, $ext, true);

    }

    public function getAdminPlugin($name, $ext) {

        if (($name == "" && $ext == 'configxml') || ($name == ".configxml" && $ext == 'children')) {
            $p = $this->getPluginInstance("admin_configxml", $this->mode);
            return $p;
        }
        foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:plugins/bxcms:plugin") as $node) {
            $type = $node->getAttribute("type");
            $p = $this->getPluginInstance($type, $this->mode);
            $params = $this->getAllNodeParameters($node);
            $p->setParameterAll($this->uri, $params);
            if ($p->adminResourceExists($this->uri, $name, $ext)) {
                return $p;
            }
        }

        // if we're here, we need a mock resource...
        foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:plugins/bxcms:plugin") as $node) {

            $type = $node->getAttribute("type");
            $p = $this->getPluginInstance($type, $this->mode);
            $params = $this->getAllNodeParameters($node);
            $p->setParameterAll($this->uri, $params);

            if ($p->adminResourceExists($this->uri, $name, $ext,true)) {
                return $p;
            }
        }

        return null;
    }

    public function getAdminMasterPlugin() {
        $plugin = NULL;

        $res = $this->getXPathNodes("/bxcms:bxcms/bxcms:plugins/bxcms:plugin[@adminmaster='true']");
        if($res->length > 0) {
            $node = $res->item(0);
            $type = $node->getAttribute("type");
            $plugin = $this->getPluginInstance($type, $this->mode);
            $params = $this->getAllNodeParameters($node);
            $plugin->setParameterAll($this->uri, $params);
        }

        return $plugin;
    }

    public function getChildrenPlugins() {
        $plugins = array();

        foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:plugins[not(@inGetChildren) or @inGetChildren != 'false']/bxcms:plugin") as $node) {
            $type = $node->getAttribute("type");
            try {
                $output = $this->getPluginInstance($type, $this->mode);
                $params = $this->getAllNodeParameters($node);
                $output->setParameterAll($this->uri, $params);
                $plugins[$type] = $output;
            } catch (Exception $e) {
                bx_log::log("plugin $type could not be loaded.");
            }
        }
        return $plugins;
    }

    public function getFilters($name, $ext) {
        $filters = new bx_filtermanager();
        foreach ($this->getXPathNodes("/bxcms:bxcms/bxcms:filters/bxcms:extension") as $node) {
            if($ext == $node->getAttribute('type')) {
                $res = $this->getXPathNodes("bxcms:file", $node->parentNode);
                if($res->length > 0) {
                    // filter matches only specific file names
                    foreach($res as $fileNode) {
                        if($name == $fileNode->getAttribute('name') ||
                            ($fileNode->getAttribute("preg") && preg_match($fileNode->getAttribute("preg"),$name))) {
                            $filterNodeList = $this->getXPathNodes('bxcms:filter', $node->parentNode);
                            foreach($filterNodeList as $filterNode) {
                                $filter = $this->getFilterInstance($filterNode->getAttribute('type'), $this->mode);
                                $params = $this->getAllNodeParameters($filterNode);
                                $filter->setParameterAll($this->uri, $params);
                                $filters->addFilter($filter,$name.$ext);

                            }
                        }
                    }
                } else {
                    // filter matches all filenames for an extension
                    $filterNodeList = $this->getXPathNodes('bxcms:filter', $node->parentNode);
                    foreach($filterNodeList as $filterNode) {
                        $filter = $this->getFilterInstance($filterNode->getAttribute('type'), $this->mode);
                        $params = $this->getAllNodeParameters($filterNode);
                        $filter->setParameterAll($this->uri, $params);
                        $filters->addFilter($filter,$name.$ext);
                    }
                }
            }
        }
        return $filters;
    }

    /** helper nodes **/

    protected function getXPathNodes($xpath, $ctxt = NULL) {

        $xp = new Domxpath($this->configxml);
        $xp->registerNamespace("bxcms","http://bitflux.org/config");
        if ($ctxt) {
            return  $xp->query($xpath,$ctxt);
        } else {
            return  $xp->query($xpath);
        }
    }

    protected function getPluginInstance($type, $mode) {
        $pluginname = "bx_plugins_".$type;

        if (!@class_exists($pluginname)) {
            throw new Exception("Plugin \"$type\" ($pluginname) could not be found");
        }
        $output = call_user_func(array($pluginname,'getInstance'),$mode);
        $output->name = $type;
        return $output;
    }

    protected function getFilterInstance($type, $mode) {
        $pluginname = "bx_filters_".$type;
        $output = call_user_func(array($pluginname,'getInstance'),$mode);
        $output->name = $type;
        return $output;
    }

    protected function getAllNodeParameters($ctxNode) {
        $params = array();

        $parameterNodeList = $this->getXPathNodes('bxcms:parameter', $ctxNode);
        foreach($parameterNodeList as $parameterNode) {
            $pType = $parameterNode->getAttribute('type');

            $pName = $parameterNode->getAttribute('name');
            $pValue = $parameterNode->getAttribute('value');
            $pKey = $parameterNode->getAttribute('key');
            $pType == '' ? $pType = BX_PARAMETER_TYPE_DEFAULT : $pType;
            //replace variables
            if ($this->variables && $pValue{0} == '$') {
                $vKey = substr($pValue,1);
                if (isset($this->variables[$vKey])) {
                    $pValue = $this->variables[$vKey];
                }
                
            }
            
            if(!empty($pName)) {
                if ($pPrefix = $parameterNode->getAttribute('valuePrefix')) {
                    $pValue = constant($pPrefix).$pValue;
                }
                if ($pKey) {
                    $params[$pType][$pName][$pKey] = $pValue;
                } else {
                    $params[$pType][$pName] = $pValue;
                }
            }
        }
        return $params;
    }


    public function getParameters($type = BX_PARAMETER_TYPE_DEFAULT ) {
        if (isset($this->parameters[$type])) {
            return $this->parameters[$type];
        } else {
            return array();
        }
    }


}

?>
