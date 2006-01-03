<?php

class bx_plugins_admin_edit extends bx_component implements bxIplugin {
    
    static private $instance = null;
    protected $editor  = array();
    protected $res = array();
    
    public static function getInstance($mode) {
        
        if (!bx_plugins_admin_edit::$instance) {
            bx_plugins_admin_edit::$instance = new bx_plugins_admin_edit($mode);
        } 
        
        return bx_plugins_admin_edit::$instance;
    }
    
    private function __construct ($mode) {
        
         $this->mode = $mode;
    }

    protected function getFullPath($path, $name, $ext) {
        // strip admin part of url - sort of weak
        $path = str_replace('admin/edit/', '', $path);
        return $path.$name.'.'.$ext;
    }
    
    public function getContentById($path, $id) {
        $editor = $this->getEditorById($id);
        if ($editor) {
            return $editor->getEditContentById($id);
        } else {
            return null;
        }
    }

    protected function getEditorById( $id) {
        
        if (!isset($this->editor[$id])) {
            $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
            $ps = $parts['coll']->getEditorsById($parts['rawname']);
            if (is_array($ps) && count($ps) > 0 ) {
                $editor = array_shift($ps);
                if (isset($_GET['editor'])) {
                    foreach($ps as $p ) {
                        if ($p == $_GET['editor']) {
                            $editor = $p;
                            break;
                        }
                    }
                }
                $classname = "bx_editors_".$editor;
                $this->editor[$id] = new $classname($id);
            } else {
                //redirect to the properties, if no editor was found
                header ("Location: ".BX_WEBROOT."admin/properties/$id");
                die();
            }
        }
        
        return $this->editor[$id];
        
    }
    
    public function handlePOST($path, $id, $data) {
        // get editor for this request
        $e = $this->getEditorById( $id);
        if(!empty($e)) {
            // if we have files to upload, we call the corresponding editors uploadFile method
            // FIXME: this should probably be moved to it's own method handleFileupload (invoked by the collection)
            /*if(!empty($_FILES)) {
                $e->uploadFile($path, $id, $_FILES);
            }*/
            $ret = $e->handlePOST($path, $id, $data);
        }
        return $ret;
    }


    public function getIdByRequest($path, $name=NULL, $ext=NULL) {
        
        if (substr($name,0,1) == "/") {
            $id = "$name.$ext";
        } else {
            $id = "/$name.$ext";
        }
        return $id;
    } 
    
    public function getPipelineName($path = NULL, $id = NULL) {
        $editor = $this->getEditorById( $id);
        if(!empty($editor) ) {
            return $editor->getPipelineName($path, $id);
        }
        return FALSE;
    }

    public function getStylesheetNameById($path = NULL, $id = NULL) {
        $editor = $this->getEditorById( $id);
        
        if(!empty($editor) ) {
            $parts = bx_collections::getCollectionUriAndFileParts($id,$this->mode);
            return $editor->getStylesheetNameById($parts['colluri'], $parts['rawname']);
        }
        return FALSE;
        //return $this->getEditorById( $id)->getStylesheetName(); 
    }
    
    public function isRealResource($path, $id) {
        return false;
    }
    
    public function getResourceById($path,$id) {
        return false;
    }
    
    public function getContentUriById($path, $id, $sample = false) {
         
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        return $parts['coll']->getContentUriById($parts['rawname'],$sample);   
         
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        
        $parts = bx_collections::getCollectionAndFileParts($id,$this->mode);
        return $parts['coll']->getPluginById($parts['rawname'].".".$ext,$sample);
         
    }
    
    public function stripRoot() {
        return false;   
    }
}
?>
