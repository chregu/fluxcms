<?php

class popoon_components_readers_adminpopup extends popoon_components_reader {

    
    private $requestUri = "";
    private $editItems = array();
    protected $mode = "output";
    function __construct ($sitemap) {
        
        parent::__construct($sitemap);
        $this->requestUri = $_SERVER['REQUEST_URI'];
        /*FIXME: better way to assing menu-items*/
        $this->editItems = array(   
                                /*    array('name'    => 'Edit Collection Properties'),*/
                               /*     array('name'    => 'Add New xhtml'),
                                    array('name'    => 'Add New Collection')
                                 */   
                                    );
    }
    
   
    function init($attribs) {
        parent::init($attribs);
    } 
    
    
    public function start() {

        $i18n = $GLOBALS['POOL']->i18nadmin;
        $permObj = bx_permm::getInstance(bx_config::getInstance()->getConfProperty('permm'));
        $perm = bx_permm::getInstance();
        
        
        if (!empty($this->requestUri)) {
            $strpos = strpos($this->requestUri,"admin/navi/popup");
            $path = substr($this->requestUri,$strpos+16);
            if (!$path) {
                $path = '/';
            }
        }
        
        $permId = substr($path, 0, strrpos($path, '/', -1)+1);
        
        $req = bx_collections::getCollectionAndFileParts($path);
        $col = $req['coll'];
 
        
        if ($col instanceof bx_collection) {
            
            $dom = new domDocument();
            $dom->loadXML('<adminpopup/>');
            
            switch($req['ext']) {
                case "":
                    $type='collection';
                break;
                default:
                    $type='resource';
                break;
            }
            
            if ($path == "/") {$dom->documentElement->setAttribute('root', "true");}
             
            $dom->documentElement->setAttribute('type', $type);
            
            $n=$dom->createElement('dummmy');
            $dom->documentElement->appendChild($n);
            $parts = bx_collections::getCollectionUriAndFileParts($path,$this->mode);
            $coll = bx_collections::getCollection($path, $this->mode);
            
            $editors = $col->getEditorsById($parts['rawname']);
            $editorsNode = $dom->createElement('editors');
            if (is_array($editors)) {
                foreach($editors as $e => $editor) {
                    $e = $dom->createElement('editor');
                    $e->setAttribute('name', $editor);
                    $e->setAttribute('href', sprintf(BX_WEBROOT."admin/edit%s?editor=%s", $path, $editor));
                    $e->setAttribute('target', 'edit');
                    $editorsNode->appendChild($e);
                }
            }
            
            if($permObj->isAllowed('/',array('admin'))) {
            	if ($perm->isAllowed($permId,array('collection-back-properties'))) {
	                $elem = $dom->createElement('editor');
	                $elem->setAttribute('name', "Properties");
	                $elem->setAttribute('href', BX_WEBROOT.'admin/properties'.$path);
	                $elem->setAttribute('target', 'edit');
	                $editorsNode->appendChild($elem);
            	}     	
            }
   
            $dom->documentElement->appendChild($editorsNode);
            
            if ($type == "collection" && $permObj->isAllowed('/',array('admin'))) {
                // actions for creating new resources ...
                $resourceTypes = $coll->getPluginResourceTypes();
                $resourceTypesNode = $dom->createElement('resourceTypes');
                
                if ($perm->isAllowed($permId, array('collection-back-create'))) {
	                $nc = $dom->createElement('resourceType');
	                $nc->setAttribute('name', 'Collection');
	                $nc->setAttribute('target', 'edit');
	                $nc->setAttribute('src', "javascript:admin.addNewCollection('$path');");
	                $resourceTypesNode->appendChild($nc);
                }
                
                if(!empty($resourceTypes)) {
                    
                    foreach($resourceTypes as $resourceType) {
                        
	                	if($resourceType == "xhtml") {
	                		if (!$perm->isAllowed($permId, array('xhtml-back-create'))) {
		        				continue;
		    				}
	                	} else if($resourceType == "file" or $resourceType == "archive" or $resourceType == "gallery") {
	                		if (!$perm->isAllowed($permId, array('gallery-back-upload'))) {
		        				continue;
		    				}
	                	}                        
                        
                        $e = $dom->createElement('resourceType');
                        $e->setAttribute('name', $resourceType);
                        $e->setAttribute('target', 'edit');
                        $e->setAttribute('src', "javascript:admin.addNewResource('".$path."', '$resourceType');");
                        
                        $resourceTypesNode->appendChild($e);
                        
                    }
                }
                $dom->documentElement->appendChild($resourceTypesNode);
            }   

            if($permObj->isAllowed('/',array('admin'))) {

                /* other actions (delete,rename, ...) */
                $actionsNode = $dom->createElement('actions');
                
                if ($path != "/") {
                    
                	if ($perm->isAllowed($permId,array('collection-back-copy'))) {
	                     $actionCopy = $dom->createElement('action');
	                    $actionCopy->setAttribute('name', $i18n->translate('Copy'));
	                    $actionCopy->setAttribute('src', 'javascript:admin.copyResource("'.$path.'");');
	                    $actionsNode->appendChild($actionCopy);
                	}
                    
                    if ($perm->isAllowed($permId,array('collection-back-delete', 'collection-back-copy'))) {
	                    $actionCopy = $dom->createElement('action');
	                    $actionCopy->setAttribute('name', $i18n->translate('Move'));
	                    $actionCopy->setAttribute('src', 'javascript:admin.copyResource("'.$path.'",true);');
	                    $actionCopy->setAttribute('lineAfter', 'true');
	                    $actionsNode->appendChild($actionCopy);
                    }
	                if ($perm->isAllowed($permId,array('collection-back-delete'))) {    
	                    // delete
	                    
	                    
	                    $actionDelete = $dom->createElement('action');
	                    $actionDelete->setAttribute('name', $i18n->translate('Delete'));
	                    
	                    $actionDelete->setAttribute('src', 'javascript:admin.deleteResource("'.$path.'");');
	                    $actionsNode->appendChild($actionDelete);
                    }
                }
                $dom->documentElement->appendChild($actionsNode);
            }
        }
        
        
            
        
        if (is_array($this->editItems)) {
            foreach($this->editItems as $i => $item) {
                
                $elem = $dom->createElement('item');
                if (is_array($item)) {
                    foreach($item as $name => $attrib) {
                        $elem->setAttribute($name, $attrib);
                    }
                }
                
                $dom->documentElement->appendChild($elem);
            }
            
            
        }

        
        header("Content-type: text/xml");
        print $dom->saveXML();
    }
    
    function appendEditConfigXml($path,$file,$actionsNode, $dom) {
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $action = $dom->createElement('action');
        if (file_exists(BX_DATA_DIR.$path.$file)) {
            $action->setAttribute('name', $i18n->translate2('Edit {file}', array('file'=>$file)));
        } else {
            $action->setAttribute('name', $i18n->translate2('Add {file}', array('file'=>$file)));
        }
        $action->setAttribute('target', 'edit');
        $action->setAttribute('src', BX_WEBROOT.'admin/edit'.$path.$file.'?editor=oneform');
        return $actionsNode->appendChild($action);
        
    }
    
}

?>
