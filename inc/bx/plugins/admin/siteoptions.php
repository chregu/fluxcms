<?php
class bx_plugins_admin_siteoptions extends bx_plugins_admin implements bxIplugin  {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_siteoptions::$instance) {
            bx_plugins_admin_siteoptions::$instance = new bx_plugins_admin_siteoptions($mode);
        } 
        
        return bx_plugins_admin_siteoptions::$instance;
    }
    

   /* protected function getFullPath($path, $name, $ext) {
        return $path.$name;
    }*/
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if ($ext) {
            return $name.".$ext";    
        } else if ($name == '') {
            return '/';
        } else {
            return $name;
        }
        
    }
    
    public function getContentById($path, $id) {
        $coll =  bx_collections::getCollection($id,"output");
        $resourceType = "siteoptions";
        $xml = $this->getAddResourceParams($resourceType,$coll->uri);
        return $xml;

    }
    
    protected function addOption($name, $type, $dom, $options = array()) {
        $node = $dom->createElement('field');
        $node->setAttribute('name', $name);
        $node->setAttribute('type', $type);
        if ($value = bx_config::getConfProperty($name)) {
            if (is_array($value)) {
                if (isset($options['ArrayAsNewline']) && $options['ArrayAsNewline']) {
                    $value = implode("\n",$value);
                } else {
                    $value = implode(";",$value);   
                }
            }
            $node->setAttribute('value',$value);
        }
        if (isset($options['help'])) {
            $helpnode = $dom->createElement("help",$options['help']);
            $node->appendChild($helpnode);
        }
        $dom->documentElement->appendChild($node);
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
    
    protected function getAddResourceParams($type,$uri) {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $dom = new DomDocument();
     
      
        $fields = $dom->createElement('fields');
        $dom->appendChild($fields);
        
        $this->addOption("sitename","text",$dom,array("help"=>$i18n->translate("Name of the site")));
        $this->addOption("sitedescription","text",$dom,array("help"=>$i18n->translate("Subtitle for the site")));
        
        $templNode = $dom->createElement('field');
        $templNode->setAttribute("name", 'theme');
        $templNode->setAttribute("type", 'select');
        $templNode->setAttribute("img","themePreview");
        $theme = bx_config::getConfProperty("theme");
        $helpnode = $dom->createElement("help",$i18n->translate('motiv_help'));
        $templNode->appendChild($helpnode);
        
        foreach($this->getTemplates() as $t => $template) {
            $templ = $dom->createElement('option');
            $templ->setAttribute("name", $template['name']);
            $templ->setAttribute("value", $template['name']);
            if ($template['name'] == $theme) {
                $templ->setAttribute("selected","selected");
            }
            $optfield = $dom->createElement('field');
            $optfield->setAttribute("name", 'themeCss');
            $optfield->setAttribute("type", 'select');
            $templ->appendChild($optfield);
            $cssSelected=bx_config::getConfProperty("themeCss");
            foreach($template['css'] as $css) {
                
                $opt = $dom->createElement("option");
                $opt->setAttribute("name",$css);
                $opt->setAttribute("value",$css);
                if ($template['name'] == $theme && $cssSelected == $css) {
                    $opt->setAttribute("selected","selected");
                }
                $optfield->appendChild($opt);
                
            }
            
            
            
            
            $templNode->appendChild($templ);
        }
        $fields->appendChild($templNode);
        
        $this->addOption("blogname","text",$dom,array("help"=>$i18n->translate("blogname_rssfeed")));
        $this->addOption("blogdescription","text",$dom,array("help"=>$i18n->translate("desc_rssfeed")));
        
        
        $this->addOption("outputLanguages","text",$dom,array("help"=>$i18n->translate("help_outputlanguages")));
        $this->addOption("defaultLanguage","text",$dom,array("help"=>$i18n->translate("help_defaultlanguage")));
        
        $this->addSelectOption("blogDefaultEditor",array(
           'wyswiyg' => "WYSIWYG Editor - Firefox and IE/Windows only (FCKEditor)",
           'source' => "Source Editor - works with any Browser (textarea style)"),
           $dom,array("help"=>$i18n->translate("Blog Default Editor")));
        
        
        $this->addSelectOption("blogDefaultPostCommentMode",array(
           1 => "Allow comments for 1 month",
           2 => "Always allow comments",
           3 => "No comments allowed"), $dom,array("help"=>$i18n->translate("default_blogcommentmode")));
        
        $this->addSelectOption("blogSendRejectedCommentNotification",array(
        'false' => "No",
           'true' => "Yes")
           , $dom,array("help"=>$i18n->translate("help_blogSendRejectedCommentNotification")));
       
       $this->addOption("blogCaptchaAfterDays","text",$dom,array("help"=>$i18n->translate("blogCaptchaAfterDays")));
        
       $this->addOption("blogWeblogsPing","textarea",$dom,array("help"=>$i18n->translate("help_blogWeblogsPing"),"ArrayAsNewline"=>true));
       
        $this->addOption("copyright","text",$dom,array("help"=>$i18n->translate("help_copyright")));
        
        $this->addOption("cclink","text",$dom,array("help"=>$i18n->translate("help_cclink")));
        
        
       $this->addOption("timezoneSeconds","text",$dom,array("help"=>$i18n->translate("help_timezoneseconds")));
       $this->addOption("timezoneString","text",$dom,array("help"=>$i18n->translate("help_timezonestring")));
        
       $this->addOption("ICBM","text",$dom,array("help"=>$i18n->translate("help_icbm")));
        $this->addOption("image_allowed_sizes","text",$dom,array("help"=>$i18n->translate("help_image_allowed_sizes")));
        
        
        
        return $dom;
     
    }
    
    protected function getTemplates() {
        
        $d = new DirectoryIterator(BX_THEMES_DIR);
        $themes = array();
        foreach ($d as $file) {
            $filename = $file->getFileName();
            if ($filename == '.' || $filename == '..' || substr($filename,0,1) == ".") {
                continue;
            }
            if ($file->isDir()) {
                if ($filename != "standard" && file_exists(BX_THEMES_DIR.$filename."/css/")) {
                    $themes[$filename]['name'] = $filename;
                    $themes[$filename]['css'] = $this->getCss(BX_THEMES_DIR.$filename."/css/");
                    
                    
                }
            }
        }
        return $themes;
        
    }
    
    protected function getCss($dir) {
        $css = array();
        if (file_exists($dir)) {
            $d = new DirectoryIterator($dir);
            foreach ($d as $file) {
                $filename = $file->getFileName();
                if ($filename == '.' || $filename == '..' || substr($filename,0,1) == ".") {
                    continue;
                }
                if ($file->isFile()) {
                    
                    if (strpos($filename,"main") === 0) {
                        $css[$filename] = $filename;
                    }
                    
                    
                }
            }
            ksort($css);
        } 
        return $css;
        
    }
    
    
    /* FIXME:: this should be cleaned up. arguments are $path,$id,$data,$mode */
    public function handlePost($path, $name, $ext, $data=null) {
        
        if ($data == NULL) {
            $data = $_REQUEST['bx']['plugins']['admin_siteoptions'];
            $data = bx_helpers_globals::stripMagicQuotes($data);
        }
        
        foreach ($data as $name => $value) {
          if ($name == 'cclink' && $value && bx_config::getConfProperty($name) != $value) {
                $license = $this->getCCLicense($value);
                if ($license) {
                    bx_config::setConfProperty('cclicense',$license);
                }
          } 
          bx_config::setConfProperty($name,$value);
      }
      if (isset($GLOBALS['POOL']->config->cacheDBOptions) && $GLOBALS['POOL']->config->cacheDBOptions) {
          @unlink(BX_TEMP_DIR.'/config.inc.php');
      }
        
    }
    
    protected function getCCLicense($value) {
        $sc = popoon_helpers_simplecache::getInstance();
        $html = $sc->simpleCacheHttpRead($value,3600);
        
        $dom = new domdocument();
        @$dom->loadHTML($html);
        $xp = new domxpath($dom);
        foreach ($xp->query("/html/body/comment()") as $node) {
            $l = $node->nodeValue;
            if (strpos($l,'<rdf:RDF ')!== false) {
                $license = str_replace("</rdf:RDF>",'<Work rdf:about="">
  <license rdf:resource="'.$value.'" />
</Work>
</rdf:RDF>',$l);
                return $license;
            }
        }
        return null;
    }
    


    protected function getParentCollection($path) {
        $parent = dirname($path);
        if ($parent != "/"){
            $parent .= '/';
        }
        
        return bx_collections::getCollection($parent,"output");
    }

    protected function getAction() {
        return !empty($_GET['action']) ? $_GET['action'] : FALSE;
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

    public function getStylesheetNameById($path = NULL, $id = NULL) {
        return 'addresource.xsl';
    }
    
   public function getPipelineName($path = NULL, $name = NULL, $ext = NULL) {
        return "standard";
    }
    
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
    public function getCommentCaptchaDays() {
        return $GLOBALS['POOL']->config->blogCaptchaAfterDays;
    }
    
}
?>
