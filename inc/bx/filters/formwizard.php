<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+


class bx_filters_formwizard extends bx_filter {
    
    static private $instance = NULL;

    public $lang = 'de';
    public $defaultLang = 'de';
    protected $internFields = array();
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_filters_formwizard($mode);
        } 
        return self::$instance;
    }   
    
    public function preHTML($xml, $collUri) {
        @session_start();
        $this->collUri = $collUri;
        $this->lang =  $GLOBALS['POOL']->config->getOutputLanguage();
        $this->defaultLang = $GLOBALS['POOL']->config->getDefaultOutputLanguage();
        $ctxt = new Domxpath($xml);
        $ctxt->registerNameSpace('forms', 'http://bitflux.org/forms');
        
        $xforms = $ctxt->query("//forms:formwizard");
        $wizardnode = $xforms->item(0);
        
        if(empty($wizardnode)) {
           return;
        }
        
        $xslAttribute = $wizardnode->getAttribute("xsl");
        if(!empty($xslAttribute) && is_readable(BX_LIBS_DIR."/filters/formwizard/xml2html.xsl")) {
            $xslSrc = BX_LIBS_DIR."/filters/formwizard/xml2html.xsl";
        } else {
            $xslSrc = BX_LIBS_DIR."/filters/formwizard/xml2html.xsl";
        }
        
        $xsl = new XsltProcessor();
        $xslDom = new DomDocument();
        $xslDom->load($xslSrc);
        $xsl->importStylesheet($xslDom);
        $xsl->registerPhpFunctions();
        if ($wizardnode) {
            
            // create domxml object from config xml
            $src = $wizardnode->getAttribute("src");
            foreach ($wizardnode->childNodes as $child) {
                if ($child->nodeType == 1 && $child->localName == "parameter") {
                    if ($child->getAttribute("type") == "noform") {
                        $this->internFields[$child->getAttribute("name")] = $child->getAttribute("value");
                    }
                }
            }
            
            $this->config = new DomDocument();
            $this->config->load(BX_PROJECT_DIR."/$src");
            
            $this->confctxt = new DOMxpath($this->config);
            $this->confctxt->registerNameSpace("bxco","http://bitflux.org/config/1.0");
            
            $cookiename = $this->config->documentElement->getAttribute("cookiename");
            
            
            // get current screen id
            if(!empty($_POST['thisPage'])) {
                $currentScreenID = $_POST['thisPage'];
            } else {
                // if no ID is given, take the one from the first screen
                $screens = $this->confctxt->query("/bxco:wizard/bxco:screen");
                
                $screen = $screens->item(0);
                if(!empty($screen)) {
                    $firstScreen = $screen;
                    $currentScreenID = $firstScreen->getAttribute('id');
                }
            }
           
            $showErrors = true;
            if ($cookiename && !empty($_COOKIE[$cookiename])) {
                foreach (unserialize(bx_helpers_globals::stripMagicQuotes($_COOKIE[$cookiename])) as $key => $value) {
                    if (!isset($_REQUEST["bx_fw"][$key])) {
                        $_REQUEST["bx_fw"][$key] = $value;
                    }
                }
                if (empty($_POST['thisPage'])) {
                    $_POST['thisPage'] = $currentScreenID;
                    $showErrors = false;
                }
            }
            
            // append nodes for sqlvalues
            $sqlValuesNodes = $this->confctxt->query("/bxco:wizard/bxco:screen[@id = '".$currentScreenID."']//bxco:sqlvalues");
            foreach($sqlValuesNodes as $node) {
                $query = $node->getAttribute('query');
                $query = $this->replacePlaceholder($query);
                
                $valueNodes = $this->getValueNodesFromSQL($query, 'option');
                $fieldNode = $node->parentNode;
                
                foreach($valueNodes as $valueNode) {
                    $fieldNode->appendChild($valueNode);
                }
            }
            
            
            // a page has been posted. this generates an array representing all the fields on the current screen
            if (isset($_POST['thisPage'])) {
                $allfields = $this->confctxt->query("/bxco:wizard/bxco:screen[@id = '".$_POST["thisPage"]."']//bxco:field");
                foreach($allfields as $node) {
                    $fields[$node->getAttribute("name")] = $node;
                    if ($node->getAttribute("type") == "checkboxtext") {
                        $fields[$node->getAttribute("name")."_text"] = $node;
                    }
                    else if ($node->getAttribute("type") == "radio") {
                        foreach($node->childNodes as $childnode) {
                            
                            if ($childnode->nodeName == "option") {
                                if ($childnode->getAttribute("type") == "text") {
                                    $fields[$childnode->getAttribute("name")."_text"] =  $childnode ;
                                    
                                }
                            }
                        }
                    }
                }
            }
            
            // replace values of all fields with type requestVar
            if(!empty($_REQUEST)) {
                $requestVarFields = $this->confctxt->query("/bxco:wizard/bxco:screen//bxco:field[@type='requestVar']");
                foreach ($requestVarFields as $node) {
                    // copy value from correspondig request var if type is requestVar 
                    $name = $node->getAttribute('name');
                    if(!empty($_REQUEST[$name])) {
                        $fields[$name] = $node;
                        if (get_magic_quotes_gpc()) {
                            $fields[$name]->setAttribute('value', stripslashes($_REQUEST[$node->getAttribute('name')]));
                        } else {
                            $fields[$name]->setAttribute('value', ($_REQUEST[$node->getAttribute('name')]));
                        }
                        
                    }
                }
            }
            
            if(!isset($_SESSION["bx_wizard"]) || !is_array($_SESSION["bx_wizard"])) {
                $_SESSION["bx_wizard"] = array();
            }
            
            $error = FALSE;
            // $fields is set above, so we have to check the posted values
            if ( isset($fields) && is_array($fields)) {
                foreach($fields as $key => $node) {
                    if(isset($_REQUEST["bx_fw"][$key]) && ($_REQUEST["bx_fw"][$key] != '')) {
                        if (get_magic_quotes_gpc()) {
                            $value = stripslashes($_REQUEST["bx_fw"][$key]);
                        } else {
                            $value = ($_REQUEST["bx_fw"][$key]);
                        }
                    } else {
                        $value = '';
                    }
                    
                    if ($node->getAttribute("required") == "y" &&  strlen(trim($value)) == 0) {
                        if ($showErrors) {
                            $node->setAttribute("error", "required");
                        }
                        $error = true;
                    }
                    
                    $tp = $node->getAttribute("type");
                    $nv = $node->getAttribute("value");
                    
                    if ($tp == "session") {
                        $bx_wizard_fields[$key] = $_SESSION["auth"]["fields"][$node->getAttribute("value")];
                    } elseif ($tp == "datetime") {
                        $bx_wizard_fields[$key] = date(sprintf("%s", $node->getAttribute("format"))); 
                    } elseif (preg_match("#([a-z]{1,})\:\/\/(.*)#", $nv, $matches) && empty($value)) {
                        $scheme = sprintf("_%s", strtoupper($matches[1])); 
                        if (isset($GLOBALS[$scheme][$matches[2]])) {    
                            $bx_wizard_fields[$key] = $GLOBALS[$scheme][$matches[2]];
                            $node->setAttribute("value", utf8_encode($GLOBALS[$scheme][$matches[2]]));
                        }

                    } else {
                        if (strlen($value) > 0 ) {
                            $node->setAttribute("value", $value);
                        }
                        
                        $bx_wizard_fields[$key] = $value;
                    }
                }
                $_SESSION["bx_wizard"] = array_merge($_SESSION["bx_wizard"],$bx_wizard_fields);
            }
            
            $params = array();
            
            // if an error has occured, we stay at the current screen
            if (isset($error) && $error) {
                $params['screenid'] = $_POST['thisPage'];
                $screen = false;
            }
            else {
            // if there was no error, we call the appropriate method
                $screenId = (isset($_POST['thisPage'])) ? $_POST['thisPage']:'__nopost';
                 $screen = $this->confctxt->query("/bxco:wizard/bxco:screen[@id = '".$screenId."']");
                $screen = $screen->item(0);
            }
               
            
            if (isset($_POST["thisPage"]) && !$error) {
                // handle newsletter subscribe/unsubscribe
                if($screen->hasAttribute('newsletterSubscribeField') || $screen->hasAttribute('newsletterUnsubscribeField')) {
                    $fields = $this->getFields();
                    $email = 'email';
                    if($screen->hasAttribute('newsletterEmailField')) {
                        $email = $screen->getAttribute('newsletterEmailField');
                    }
                    
                    if($screen->hasAttribute('newsletterSubscribeField') && isset($fields[$screen->getAttribute('newsletterSubscribeField')])) {
                        if(($fields[$screen->getAttribute('newsletterSubscribeField')] == 1) && ($fields[$email] != '')) {
                            bx_filters_formwizard_methods_mailman::subscribe($fields['email'], array('lang'=>$this->lang));
                        }
                    }
                    if($screen->hasAttribute('newsletterUnsubscribeField') && isset($fields[$screen->getAttribute('newsletterUnsubscribeField')])) {
                        if(($fields[$screen->getAttribute('newsletterUnsubscribeField')] == 1) && ($fields[$email] != '')) {
                            bx_filters_formwizard_methods_mailman::unsubscribe($fields['email']);
                        }
                    }
                }
            }
                //screen has a method from an external class
                
                if ($screen && $screen->hasAttribute("class")) {
                    
                    $class = $screen->getAttribute("class");
                    $method = $screen->getAttribute("method");
                    
                    /*FIXME: this is ugly and needs to be done properly */
                    if (($ret = $this->callExternalMethod($class, $method)) === FALSE) {
                        foreach($this->config->getElementsByTagName('screen') as $n => $snode) {
                             if ($snode->getAttribute('id') == $screenId) {
                                $this->config->getElementsByTagName('screen')->item($n)->setAttribute('error', 'contact_noformselected'); 
                                break;
                             }
                        
                        }
                        
                        $error=true;
                                        
                    } 
                        
                } elseif ($screen && $screen->hasAttribute("method")) {
                    // screen has a method from this component
                    $method = $screen->getAttribute("method");
                    $this->$method();
                }
            
            
            // if there is a next screen, set it now
            if (isset($_POST['nextPage']) && !$error) {
                $params['screenid'] = $_POST['nextPage'];
            }
            
            // pass some parameters
            $params['lang'] = $this->lang;
            $params['requestUri'] = $_SERVER['REQUEST_URI'];
            $params['webroot'] = BX_WEBROOT;
            // process config xml and create some html out of it
            foreach($params as $key => $value) {
                $xsl->setParameter('', $key, $value);
            }
            
            //var_dump($this->config->saveXML());
            $xsl->registerPhpFunctions();
            $result = $xsl->transformToDoc($this->config);
            $parent = $wizardnode->parentNode;
            $subxml=$result->documentElement;
            //$subxml = $this->config->documentElement; /   /$result->documentElement;
            
            // replace the formwizard node in the article
            if ($subxml) {
                $subxml = $xml->importNode($subxml, true);
                $parent->replaceChild($subxml, $wizardnode);
            }
        }
    }

    function getFields() {        
        
        $fields = $this->internFields;
        $allfields = $this->confctxt->query("/bxco:wizard/bxco:screen//bxco:field[@type!= 'msg']");
        foreach($allfields as $node) {
            $fields[$node->getAttribute("name")] = @$_SESSION["bx_wizard"][$node->getAttribute("name")] ;
            
            if ($node->getAttribute("type") == "checkboxtext") {
                $fields[$node->getAttribute("name")."_text"] =  $_SESSION["bx_wizard"][$node->getAttribute("name")."_text"];
            } 
            
            else if ($node->getAttribute("type") == "radio") {
                foreach($node->childNodes as $childnode) {
                    
                    if ($childnode->nodeName == "option") {
                        if ($childnode->getAttribute("type") == "text") {
                            $fields[$childnode->getAttribute("name")."_text"] =  utf8_decode($_SESSION["bx_wizard"][$childnode->getAttribute("name")."_text"]);
                        }
                    }
                }
            }
          
        }
        return $fields;
    }
    
    function getValueNodesFromSQL($query, $nodeName) {
        $nodes = array();
        $db = $GLOBALS['POOL']->db;
        $res = $db->query($query);
        if (!MDB2::isError($res)) {
            if($res->numRows() > 0) {
                while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $node = $this->config->create_element_ns('http://bitflux.org/config/1.0', $nodeName);
                    $node->set_attribute('name', utf8_encode($row['name']));
                    $node->set_attribute('value', utf8_encode($row['value']));
                    $nodes[] = $node;
                }
            }
        }
        return $nodes;
    }
    
    public function lookup($name) {
        // this could be done with one xpath-query
        // try reequested language
        $entryNS = $this->confctxt->query("//bxco:entry[@ID='$name']/bxco:text[@lang='".$this->lang."']");
        $entryNode = $entryNS->item(0);
        if(!empty($entryNode)) {
            $childNode = $entryNode->firstChild;
            if(!empty($childNode)) {
                $text = $childNode->nodeValue; 
                if(!empty($text)) {
                    return $text;
                }
            }
        }
        
        // try with default language                          
        $entryNS = $this->confctxt->query("//bxco:entry[@ID='$name']/bxco:text[@lang='".$this->defaultLang."']");
        $entryNode = $entryNS->item(0);
        if(!empty($entryNode)) {
            $childNode = $entryNode->firstChild;
            if(!empty($childNode)) {
                $text = $childNode->data; 
                if(!empty($text)) {
                    return $text;
                }
            }
        }
        
           // try any language                          
        $entryNS = $this->confctxt->query("//bxco:entry[@ID='$name']/bxco:text");
        $entryNode = $entryNS->item(0);
        if(!empty($entryNode)) {
            $childNode = $entryNode->firstChild;
            if(!empty($childNode)) {
                $text = $childNode->data; 
                if(!empty($text)) {
                    return $text;
                }
            }
        }
        
        return $name;
    }


    function emailFields($emailBodyID = '') {
        $fields = $this->getFields();
        
        $screenNode = $this->confctxt->query("/bxco:wizard/bxco:screen[@emailTo]");
        $screenNode = $screenNode->item(0);
        
        $emailTo = $screenNode->getAttribute("emailTo"); 
        $emailFrom = $screenNode->getAttribute('emailFrom');
         
        if ($emailTo) {
            $emailSubject = $screenNode->getAttribute("emailSubject");

            $bodyID = $screenNode->getAttribute('emailBodyID');
            
            if(!empty($bodyID)) {
                $emailBodyID = $bodyID;
            }
            
            if(!empty($emailBodyID)) {
                $emailBody = utf8_decode($this->lookup($emailBodyID));
                $this->_replaceTextFields($emailBody, $fields);
            } else {
                $emailBody = "";
                foreach ($fields as $key => $value) {
                    $emailBody .= "$key: $value\n";
                }
            }
            
            $headers = '';
            
            if(!empty($emailFrom)) {
                $headers .= "From: $emailFrom\r\n";
            }

            mail($emailTo, $emailSubject, $emailBody, $headers);
        } else {
            print "no email info found";
        }

        $_SESSION["bx_wizard"] = array();
        return TRUE;
    }
 
    
    /**
    *
    * Call external Methods
    *
    * @param    string  $class      classname
    * @param    string  $method     the method to call
    * @return   mixed               return fom external method    
    * @access   private
    *
    */
    function callExternalMethod($class, $method) {
        $inclfile = sprintf("%sfilters/formwizard/methods/%s.php", BX_LIBS_DIR, $class);
        if (is_readable($inclfile)) {
            include_once($inclfile);
        }
        
        $extClass = sprintf("bxfw_%s", $class);
        if (class_exists($extClass)) {
            $extObj = new $extClass($this);
            if (method_exists($extObj, $method)) {
                return $extObj->$method();
            }
        
        }
    }

    function _replaceTextFields(&$subject, $textfields) {
        //var_dump($textfields); asdf();
        foreach($textfields as $field => $value) {
            $patterns[] = '/\{'.$field.'\}/';
            $replacements[] = $value;
        }
        $subject = preg_replace($patterns, $replacements, $subject);
    }

}


?>
