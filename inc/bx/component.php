<?php
/**
* class bx_component
* @package bx
*/

class bx_component {
    
    private $parameters = array();
    protected $mode = "output";
    protected $currentRequest = array();
    
    
    public function setParameterAll($collUri, $params) {
        if(!empty($params)) {
            if (!isset($this->parameters[$collUri])) {
                $this->parameters[$collUri] = array();
            }
            $this->parameters[$collUri] = $params;
            return TRUE;
        }
        return FALSE;
    }
    
    public function setParameter($collUri, $name, $value, $type = BX_PARAMETER_TYPE_DEFAULT) {
        if (!isset($this->parameters[$collUri])) {
                $this->parameters[$collUri] = array();
         }
         
        $this->parameters[$collUri][$type][$name] = $value;
    }
    
    public function getParameter($collUri, $name = NULL, $type = BX_PARAMETER_TYPE_DEFAULT, $default = NULL) {
        if(isset($this->parameters[$collUri][$type][$name])) {
            return $this->translateScheme($this->parameters[$collUri][$type][$name]);
        }
        return $default;
    }
    
    public function getParameterAll($collUri, $type = BX_PARAMETER_TYPE_DEFAULT) {
        if (!(isset($this->parameters[$collUri]) && isset($this->parameters[$collUri][$type]))) {
            return NULL;
        }
        return $this->parameters[$collUri][$type];
    }
    
    protected function getXMLNodeByParameters($collUri, $params = array(), $type = BX_PARAMETER_TYPE_DEFAULT) {
        $dom = new domDocument();
        $paramsNode = $dom->createElement('parameters');
        
        foreach($params as $param) {
            $value = $this->getParameter($collUri, $param, $type);
            if(isset($value)) {
                $node = $dom->createElement('parameter');
                $node->setAttribute('name', $param);
                $node->setAttribute('value', $value);
                $paramsNode->appendChild($node);
            }
        }

        return $paramsNode;
    }
    
    
    //more or less a copy&paste from popoon_sitemap::translateScheme...
    // maybe the 2 can be merged somehow
    protected function translateScheme($value,  $doNotTranslate = array()) {
        
        // don't do anything, if we don't have any scheme stuff in the $value;
        // strpos should be rather fast, i assume.
        if(is_object($value) || strpos($value,":/") === false && strpos($value,"{") === false) {
                return $value;
        }
        
        $scheme = popoon_sitemap::getSchemeParts($value);
        
        $scheme["value"] = preg_replace("#\{([^}]+)\}#e","bx_component::translateScheme('$1')",$scheme["value"] );
        
        if (in_array($scheme["scheme"],$doNotTranslate)) {
            return $value;
        } else if ($scheme['scheme'] == 'bx') {
            switch ($scheme['value']) {
                    
                case "collUri":
                    return $this->getCurrentRequest('collUri');
                case "id":
                    return $this->getCurrentRequest('id');                
                case "uid":
                    return $GLOBALS['POOL']->config->uniqueId;
                case "filename":
                    $id = $this->getCurrentRequest('id');
                       
                    if ($pos = strrpos($id,".")) {
                        return substr($id,0,$pos);
                    } else {
                        return $id;
                    }
                case "fileNumber":
                    // FIXME: mmmg, fileNumber could change within one request...
                    return $GLOBALS['POOL']->config->currentFileNumber; 
                case "lang":
                    return $GLOBALS['POOL']->config->getOutputLanguage();
            }
            if (strpos($scheme['value'],"collUriPart") !== false) {
                $count = substr_count($scheme['value'],'../');
                $parts = explode("/",trim($this->getCurrentRequest('collUri'),"/"));
                return $parts[count($parts) - $count -1];
            } else if (strpos($scheme['value'],"collUri") !== false) {
                $count = substr_count($scheme['value'],'../');
                $parts = explode("/",trim($this->getCurrentRequest('collUri'),"/"));
                for ($i = 0; $i < $count; $i++) {
                    array_pop($parts);   
                }
                return "/".implode("/",$parts)."/";
            }
                
        } else if ($scheme["scheme"] == "match") {
            
            $tokens = token_get_all("<?php " . bx_component::translateScheme($scheme["value"]) .';?>');
            $argpos = false;
            foreach ($tokens as $token) {
                    switch ($token[0]) {
                        case T_STRING:
                            $function = $token[1];
                            break;
                        case T_CONSTANT_ENCAPSED_STRING:
                            if ($argpos !== false) {
                                $args[$argpos] = substr($token[1],1,-1);
                            }
                            break;
                        case '(':
                            $argpos = 0;
                            break;
                        case ',':
                            $argpos++;
                            break;
                        case ')':
                            $argpos = false;
                    }
            }
            if (isset($function)) {
                switch ($function) {
                    case "preg":
                        preg_match($args[0],$args[1],$matches);
                        if (isset($matches[1])) {
                            return $matches[1];
                        } else {
                            return $args[1];
                        }
                }
            } else {
                return $scheme['value'];
            }
                
         
        }
        
        else if ($scheme["scheme"] != "default") {
            
            if (!@include_once("popoon/components/schemes/".$scheme["scheme"].".php")) {
                return $value;
            }
            
            return call_user_func("scheme_".$scheme["scheme"],$scheme["value"],$this);
            
        }
        else {
            return $scheme["value"];
        }
        
    }
    
    public function setCurrentRequest($collUri,$id) {
        array_push($this->currentRequest,array($collUri,$id));
    }
    
    public function getCurrentRequest($type = NULL) {
        $cur = $this->currentRequest[count($this->currentRequest)-1];
        switch ($type)  {
               case "collUri":
                        return $cur[0];
                        break;
               case "id":
                        return $cur[1];
                        break;
               default:
                        return array('collUri' => $cur[0], 'id' => $cur[1]);
        }
    }
    
    public function removeCurrentRequest($collUri, $id) {
        $pop = array_pop($this->currentRequest);
        if ($pop[0] != $collUri || $pop[1] != $id) {
            throw new Exception ("Current Request Error: CollectionUri $collUri != " . $pop[0] . " and/or id: $id !=  " . $pop[1]);
        }
    }
    
    
    
}

?>
