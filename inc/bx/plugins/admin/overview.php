<?php

class bx_plugins_admin_overview extends bx_plugin {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_overview::$instance) {
            bx_plugins_admin_overview::$instance = new bx_plugins_admin_overview($mode);
        } 
        return bx_plugins_admin_overview::$instance;
    }
    
    public function getContentById($path, $id) {
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('overview'));
        
        
         
        return $this->getSections($id); 
    }

    protected function getSections($path) {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));
        
        $parts  = bx_collections::getCollectionAndFileParts($path, $this->mode);
        $coll = $parts['coll'];
        $id = $parts['rawname'];
        $sections = array();
        $dom = new domdocument();
        $root = $dom->appendChild($dom->createElement("overview"));
        $root->setAttribute("collectionUri",$path);

        if ($id == "") {
            if ($path == "/") {
                
                if($coll instanceof bx_collection) {
                    $children = $coll->getChildren();
                    foreach($children as $child) {
                        if($child instanceof bx_collection) {
                            $this->getSubSections($dom,$child,true);
                        }
                    }
                }
                
                $this->getSubSections($dom,$coll);

				$perm = bx_permm::getInstance();
                if($permObj->isAllowed('/',array('admin'))) {
                    $opt = new bx_domdocs_overview();
                    $opt->setTitle("General Options");
                    $opt->setIcon("options");
                    if ($perm->isAllowed('/permissions/',array('permissions-back-siteoptions'))) {
                    	$opt->addLink("Edit Site-Options","siteoptions/");
                    }
			        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-users'))) {
				        $opt->addLink("Edit Users","../forms/users/");
			        }                     
                    
                    if ($perm->isAllowed('/permissions/',array('permissions-back-themes'))) {
                    	$opt->addLink("Download more themes","/themes/");
                    }
                    
                    $root->appendChild($dom->importNode($opt->documentElement,true));
                }
                
                if (isset($GLOBALS['POOL']->config->adminOverviewInfoBoxes)) {
                    foreach($GLOBALS['POOL']->config->adminOverviewInfoBoxes as $box) {
                        if ($box != "null") {
                            $pinfo = call_user_func(array("bx_plugins_".$box,"getInstance"));
                            $root->appendChild($dom->importNode( $pinfo->getOverviewSections($path,true)->documentElement,true));
                        }
                    }
                }
        
            } else {
                 $this->getSubSections($dom,$coll);
            }
           
        } else {

            $editors = $coll->getEditorsById($id);
            $opt = new bx_domdocs_overview();
            $opt->setTitle($i18n->translate2('Resource {resource}', array('resource' => $path)), "Edit");
            if (is_array($editors)) {
                foreach($editors as $e => $editor) {
                    $opt->addLink($i18n->translate2("Edit in {editor}", array('editor' => $editor)), sprintf("edit%s?editor=%s", $path, $editor));
                }
            }
           
            $res = $coll->getPluginResourceById($id);
            
            if ($res) {
                $res->getOverviewSections($opt,$coll);
            }
            $root->appendChild($dom->importNode($opt->documentElement,true));
        }
        
       
        return $dom;
    }
    
    protected function getSubSections ($dom,$coll, $mainOverview = false) {
        if ($s = $coll->getAllOverviewSections($mainOverview)) {
            foreach ($s as $doc) {
                if($doc instanceof DOMDocument) 
                    $dom->documentElement->appendChild($dom->importNode($doc->documentElement,true));
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

    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getDataUri($path, $name, $ext) {
        return FALSE;
    }

    public function getEditorsByRequest($path, $name, $ext) {
        return array();   
    }

    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
    
}
?>
