<?php

class bx_editors_blog_sub_options extends bx_editors_blog_sub {
    
    private $options = array(
        'sitename', 
        'blogDefaultEditor'
    );
    
        
    public function getInstance() {
        if (!self::$instance) {
            self::$instance = new bx_editors_blog_sub_options();
        }
        
        return self::$instance;
        
    }
    
    
    public function getEditContentById($id) {
        $dom = new DomDocument();
        $i18n = $GLOBALS['POOL']->i18nadmin;
        
        $dom->appendChild($dom->createElement('options'));
        
        
        
        $this->addOption("sitename","text",$dom,array("help"=>$i18n->translate("Name of the site")));
          $this->addSelectOption("blogDefaultEditor",array(
           'wyswiyg' => "WYSIWYG Editor - Firefox and IE/Windows only (FCKEditor)",
           'source' => "Source Editor - works with any Browser (textarea style)"),
           $dom,array("help"=>$i18n->translate("Blog Default Editor")));
        
       
        
        /*foreach($this->getOptions() as $name => $value) {
            $node = $dom->createElement('option');
            $node->setAttribute('name', $name);
            $node->setAttribute('value', $value);
            $dom->documentElement->appendChild($node);
        }*/
        
        return $dom;
    }

    public function handlePOST($path, $id, $data) {
        if(isset($data['saveOptions']) && isset($data['options']) && is_array($data['options'])) {
            foreach($data['options'] as $option => $value) {
                if(in_array($option, $this->options)) {
                    bx_config::setConfProperty($option, $value);
                }
            }
        }
    }
    
       protected function addSelectOption($name, $fields, $dom, $options = array()) {
        $node = $dom->createElement('field');
        $node->setAttribute('name', $name);
        $node->setAttribute('type', "select");
        $oriValue = bx_config::getConfProperty($name);
        foreach($fields as $value => $name) {
            $templ = $dom->createElement('option');
            $templ->setAttribute("name", $name);
            $templ->setAttribute("value",$value);
            if ($value == $oriValue) {
                $templ->setAttribute("selected","selected");
            }
            $node->appendChild($templ);
        }
        
        if (isset($options['help'])) {
            $helpnode = $dom->createElement("help",$options['help']);
            $node->appendChild($helpnode);
        }
        $dom->documentElement->appendChild($node);
    }  
    protected function addOption($name, $type, $dom, $options = array()) {
        $node = $dom->createElement('field');
        $node->setAttribute('name', $name);
        $node->setAttribute('type', $type);
        if ($value = bx_config::getConfProperty($name)) {
            if (is_array($value)) {
                $value = implode(";",$value);
            }
            $node->setAttribute('value',$value);
        }
        if (isset($options['help'])) {
            $helpnode = $dom->createElement("help",$options['help']);
            $node->appendChild($helpnode);
        }
        $dom->documentElement->appendChild($node);
    }
    
    protected function getOptions() {
        $options = array();
        foreach($this->options as $option) {
            if ($value = bx_config::getConfProperty($option)) {
                if (is_array($value)) {
                    $value = implode(";",$value);
                }
                $options[$option] = $value;
            }
        }
        return $options;
    }
    
    
}

