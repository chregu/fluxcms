<?php

define('BX_PATFORMS_NS',    'http://php-tools.net/patForms/1.0');
define('BX_FORMS_NS',       'http://bitflux.org/forms');

class bx_filters_patforms extends bx_filter {
    
    static private $instance = array();
    protected $className = NULL;
    protected $methodName = 'submitFields';
    protected $configParams = array();
    protected $xml = NULL;
    protected $dataSources = array(); 
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_filters_patforms($mode);
        } 
        return self::$instance;
    }   
    
    public function preHTML($xml) {
        require_once('patForms.php');
        $this->xml = $xml;
        // look for patForms:Form tag
        $formNS = $this->getXPathNodes('//patForms:Form');
        if($formNS->length > 0) {

            // parse form configuration
            $classConfigNS = $this->getXPathNodes('//forms:form/forms:config/forms:class');
            if($classConfigNS->length > 0) {
                $configNode = $classConfigNS->item(0);
                if($configNode->hasAttribute('name')) {
                    $this->className = $configNode->getAttribute('name');
                }
                if($configNode->hasAttribute('method')) {
                    $this->methodName = $configNode->getAttribute('method');
                }
                $this->configParams = $this->getConfigParameters($configNode);
            }

            $formDOM = new DomDocument();
            $formNode = $formDOM->importNode($formNS->item(0)->parentNode, TRUE);
            $formDOM->appendChild($formNode);

            // get our form as a string
            $formXML = $formDOM->saveXML();
             
            $parser = patForms_Parser::createParser('SimpleRenderer');
            $parser->setNamespace("patForms");
            $parser->parseString($formXML);
    
            $form = $parser->getForm();
            $form->setAutoValidate('save');
            $form->setRenderer( $parser );
            
            // post to the current file
            $form->setAction('');
            
            $this->form =& $form;
            
            // create datasources
            
            foreach($this->getXPathNodes('//forms:form/forms:config/forms:datasource') as $srcNode) {
                $type = $srcNode->getAttribute('type');
                $fieldname = $srcNode->getAttribute('field');
                $datasrcObj=Null;
                if ($type) {
                    if (!isset($this->dataSources[$type])) {
                        $this->dataSources[$type] = new $type();
                    }
                    if (is_object($this->dataSources[$type])) {
                        $e = $form->getElementByName($fieldname);
                        if ($e) {
                            $e->setDataSource($this->dataSources[$type]);
                        }
                    }
                }
            }
            
            // create rules
            foreach($this->getXPathNodes('//forms:form/forms:config/forms:rule') as $ruleNode) {
                $type = $ruleNode->getAttribute('type');
                $point = $ruleNode->getAttribute('point');
                
                if($point == 'after') {
                    $point = PATFORMS_RULE_AFTER_VALIDATION;
                } else if($point == 'both') {
                    $point = PATFORMS_RULE_BOTH;
                } else {
                    $point = PATFORMS_RULE_BEFORE_VALIDATION;
                }
                
                if ($type == 'conditionalRequired') {
                    // conditional required
                    $rule = patForms::createRule('ConditionalRequired');
                    
                    // query conditions
                    foreach($this->getXPathNodes("forms:condition[@field != '' and @value != '']", $ruleNode) as $condNode) {
                        $cField = $condNode->getAttribute('field');
                        $cValue = $condNode->getAttribute('value');
                        $rule->addCondition($cField, $cValue);
                    }

                    // query required fields
                    $requiredFields = array();
                    foreach($this->getXPathNodes("forms:field[@name != '']", $ruleNode) as $fieldNode) {
                        $requiredFields[] = $fieldNode->getAttribute('name');
                    }
                    $rule->setRequiredFields($requiredFields);
                    $form->addRule($rule, PATFORMS_RULE_BEFORE_VALIDATION);
                } else if ($type == 'Email' or $type == 'EmailEN') {
                    
                    $emailFields = array();
                    $emailRules = array();
                    
                    foreach($this->getXPathNodes("forms:field[@name != '']", $ruleNode) as $fieldNode) {
                        $rule = patForms::createRule($type);
                        $emailRules[] = $rule;
                        $field = $form->getElementByName($fieldNode->getAttribute('name'));
                        $field->addRule($rule, $point);
                        $emailFields[] = $field;
                    }
                    
                } else {
                    $rule = patForms::createRule($type);
                    // init rules
                    $rule->initFormRule($this, $ruleNode,$form);
                    $form->addRule($rule, $point);
                }
            }

            // apply patForm-filters to all form-elements
            foreach($this->getXPathNodes('//forms:form/forms:config/forms:formFilter') as $formFilterNode) {
                
                $type = $formFilterNode->getAttribute('type');
                $formFilter = & patForms::createFilter($type);
                $form->applyFilter($formFilter);
                
            } 
            
            // render the form
            // set values from external sources like $_GET
            $form->setValues($this->importExternalValues());
            $formXML = $form->renderForm();
            // quick hack to replace i18n(.*) references with i18n compliant XML-tags
            $formXML = preg_replace('/(\S+)="i18n\(([^)]*)\)"/', '$1="$2" i18n:attr="$1"', $formXML);
            $formXML = preg_replace("/i18n\(([^)]*)\)/", "<i18n:text>\$1</i18n:text>", $formXML);

            // lookup forms:classoutput tag
            $classONS = $this->getXPathNodes('//forms:classoutput');
            $classONode = FALSE;
            if($classONS->length > 0) {
                $classONode = $classONS->item(0);
            }
            
            // check if form is valid
            if($form->valid == TRUE && !empty($this->className)) {
                $formFields = $form->getValues();
                $formClassInstance = new $this->className;
                if(!is_object($formClassInstance)) {
                    throw new Exception("Could not intantiate class '$this->className'. Unable to process form fields.");
                }
                if(!method_exists($formClassInstance, $this->methodName)) {
                    throw new Exception("Class $this->className has no method named '$this->methodName'. Unable to process form fields.");
                }

                // get i18n driver
                $i18nSrc = !empty($this->configParams['i18nSrc']) ? $this->configParams['i18nSrc'] : 'xml/form2mail';
                $i18n = popoon_classes_i18n::getDriverInstance($i18nSrc, $GLOBALS['POOL']->config->getOutputLocale());
                call_user_func(array($formClassInstance, 'setI18nDriver'), $this->configParams, $i18n);
                
                // process fields using configured class
                $classOutput = call_user_func(array($formClassInstance, $this->methodName), $this->configParams, $formFields);
                if($classONode !== FALSE && $formClassInstance->returnsDOM && $classOutput instanceof DOMDocument) {
                    // replace class output tag with returned DOM from handler class
                    $newClassONode = $xml->importNode($classOutput->documentElement, TRUE);
                    $classONode->parentNode->replaceChild($newClassONode, $classONode);
                }
                
                // check if we have to redirect
                if(!empty($this->configParams['redirectTo'])) {
                    header("Location: ".$this->configParams['redirectTo']);
                    die();
                }
            } else {
                // there's no class output so we have delete that node
                if($classONode !== FALSE) {
                    $classONode->parentNode->removeChild($classONode);
                }
                    
            }
            
            // replace patForms:Form node by transformed xml from form
            if (function_exists('iconv')) {
             $formXML = @iconv("UTF-8", "UTF-8//IGNORE", $formXML);   
            }
            
            $formDOM->loadXML($formXML);
            $newFormNodeXP = new DomXPath($formDOM);
            $newFormNodeNS = $newFormNodeXP->query('//form');
            $newFormNode = $xml->importNode($newFormNodeNS->item(0), TRUE);
            
            
            $formNS->item(0)->parentNode->parentNode->replaceChild($newFormNode, $formNS->item(0)->parentNode);


            // get form errors
            $formErrors = FALSE;
            if($form->isSubmitted()) {
                $formErrors = $this->getFormErrorsXML($form);
            }
            // query for form errors node
            $formErrorsNS = $this->getXPathNodes('//forms:errors');
            if($formErrorsNS->length > 0) {
                if(!empty($formErrors)) {
                    $formErrorsDOM = new DomDocument();
                    $formErrorsDOM->loadXML($formErrors);
                    $formErrorsNode = $xml->importNode($formErrorsDOM->documentElement, TRUE);
                    $formErrorsNS->item(0)->parentNode->replaceChild($formErrorsNode, $formErrorsNS->item(0));
                } else {
                    $formErrorsNS->item(0)->parentNode->removeChild($formErrorsNS->item(0));
                }
            }

            // query forms:fieldref attributes
            $formErrors = $form->getValidationErrors();
            foreach($this->getXPathNodes("//*[@forms:fieldErrorID]") as $node) {
                $fieldref = $node->getAttributeNS(BX_FORMS_NS, 'fieldErrorID');
                if(isset($formErrors[$fieldref])) {
                    $node->setAttribute('class', 'formlabelerror');
                }
                // remove all forms:fieldref attributes
                $node->removeAttributeNS(BX_FORMS_NS, 'fieldErrorID');
            }
            
            // show customerror nodes when there are form errors
            foreach($this->getXPathNodes("//forms:customError") as $node) {
                if(!$formErrors) {
                    $node->parentNode->removeChild($node);
                }
            }
            
        }

    }
   
   
    public function formHasElement($name) {
        if ($this->form) {
            foreach($this->form->getElements() as $element) {
                if ($element->getAttribute('name') == $name) {
                    return true;
                }
            }
        }
        
        return false;
    }  

   
    public function getFormErrorsXML($form) {
        $xml = '';
        
        $errors = $form->getValidationErrors();
        if(!empty($errors)) {
            $xml = '<div class="formErrors" xmlns:i18n="http://apache.org/cocoon/i18n/2.1">';
            foreach($errors as $eName => $eErrors) {
                $element = $form->getElementByName($eName);
               
                foreach( $eErrors as $row => $error ) {
                    $fieldName = $element->getAttribute('label') != '' ? $element->getAttribute('label') : $eName;
                    if ($fieldName == '__form') {
                        $fieldName = "";
                    } else {
                        $fieldName .= ":";
                    }
                    $xml .= '<div class="formError">';
                    $xml .= '<strong>'.$fieldName.'</strong> <i18n:text>'.$error['message'].'</i18n:text>';
                    $xml .= '</div>';
                }
            }
            $xml .= '</div>';
        }
        // replace i18n string in label attibutes
        $xml = preg_replace("/i18n\(([^)]*)\)/", "<i18n:text>\$1</i18n:text>", $xml);
        
        return $xml;
    }
    
    protected function getConfigParameters($ctxNode) {
        $params = array();
        
        $parameterNodeList = $this->getXPathNodes('forms:parameter', $ctxNode);
        foreach($parameterNodeList as $parameterNode) {
            $pName = $parameterNode->getAttribute('name');
            $pValue = $parameterNode->getAttribute('value');
            
            if(!empty($pName)) {
                $params[$pName] = $pValue;
            }
        }
        return $params;
    }
    
    public function getConfigParam($name) {
        if (isset($this->configParams[$name])) {
            return $this->configParams[$name];
        }

        return null;
    }
    
    public function getXPathNodes($xpath, $ctxt = NULL) {

        $xp = new Domxpath($this->xml);
        $xp->registerNameSpace('patForms', BX_PATFORMS_NS);
        $xp->registerNameSpace('forms', BX_FORMS_NS);
        if ($ctxt) {
            return  $xp->query($xpath, $ctxt);    
        } else {
            return  $xp->query($xpath);
        }
    }
    
    protected function importExternalValues() {
        $fields = array();
        if(!empty($_GET)) {
            foreach($_GET as $name => $value) {
                // TODO: apply field filter here
                $fields[$name] = $value;
            }
        }
        return $fields;
    }
    
}

?>
