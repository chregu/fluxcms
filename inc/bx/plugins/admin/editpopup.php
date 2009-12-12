<?php

class bx_plugins_admin_editpopup extends bx_plugin {

    static private $instance = NULL;
    private $editors = array();
    private $idMapper = array();
    public static function getInstance($mode) {
        if (!bx_plugins_admin_editpopup::$instance) {
            bx_plugins_admin_editpopup::$instance = new bx_plugins_admin_editpopup($mode);
        } 
        
        return bx_plugins_admin_editpopup::$instance;
    }
    
    function __construct($mode) {
        $this->mode = $mode;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if (!is_null($name)) {
                $fullpath =  $this->getFullPath($path,$name,$ext);
        } else {
            $fullpath = $path;
        }

        if (!isset($this->idMapper[$fullpath])) {
            $parts = bx_collections::getCollectionAndFileParts($fullpath,$this->mode);
            $ids =   $parts['coll']->getResourceIdsByRequest($parts['name'],$parts['ext']);
            
            if (isset($ids[0])) {
                $this->idMapper[$fullpath] = $parts['coll']->uri . $ids[0];
            } else {
                 $this->idMapper[$fullpath]  = null;
            }
        }
        return $this->idMapper[$fullpath];
    } 
    
    protected function getFullPath($path, $name, $ext) {
        $path = str_replace('admin/editpopup/', '', $path);
        return $path.$name.'.'.$ext;
    }
    
    public function getContentById($path, $id) {
        // FIXME: clean up the mess inside this method
        $id = $this->getIdByRequest($id);
        $editors = $this->getEditors( $id);
        $coll = bx_collections::getCollection($id, $this->mode);
        $mimeTypes = $coll->getPluginMimetypes();
        
        $xml = '<div xmlns="http://www.w3.org/1999/xhtml">';

        if(!empty($editors)) {
            foreach($editors as $editor) {
                
                
                $xml .= $this->createMenuEntry('Edit In '.$editor->getDisplayName(), BX_WEBROOT."admin/edit/$id&#38;editor=".$editor->name);
            }
            $xml .= $this->createMenuSeparator();
        }

        $xml .= $this->createMenuEntry('Edit Properties', BX_WEBROOT."admin/properties$id");

        if($coll->uri != '/') { 
            $xml .= $this->createMenuEntry('Edit Collection Properties', BX_WEBROOT."admin/properties".$coll->uri);
        }

        $xml .= $this->createMenuSeparator();

        foreach($mimeTypes as $ext => $mimetype) {
            $xml .= $this->createMenuEntry("Add New $ext", BX_WEBROOT."admin/addresource".$coll->uri."?type=$ext", '');
        }
        
        //$xml .= $this->createMenuSeparator();
        $xml .= $this->createMenuEntry('Add New Collection', BX_WEBROOT."admin/collection".$coll->uri, '');

        $xml .= $this->createMenuSeparator();
        $xml .= $this->createMenuEntry('Open Admin Interface', BX_WEBROOT."admin/?id=$id", '', 'admin');

        $xml .= $this->createMenuSeparator();
        $xml .= $this->createMenuEntry('Logout', '?logout', '', '');
        
        $dom = new domDocument();
        $xml .= '</div>';
        $dom->loadXML($xml);
        
        return $dom;
    }

    protected function getEditors($fullPath) {

        if (empty($this->editors[$fullPath])) {
            $parts = bx_collections::getCollectionAndFileParts($fullPath,$this->mode);
            $coll = $parts['coll'];
            $name = $parts['name'];
            $ext = $parts['ext'];
            $editors = $coll->getEditorsById($parts['name'].".".$parts['ext']);
            foreach ($editors as $editor) {
                $classname = "bx_editors_$editor";
                $e = new $classname();
                $e->name = $editor;
                $this->editors[$fullPath][] = $e;
            }
        }
        return $this->editors[$fullPath];
    }
    
    protected function createMenuEntry($title, $href, $onclick = '', $target='admin') {
        if(empty($href)) {
            $href = '#';
        }
        $ret =  '<p class="menuentry"><a href="'.$href.'" ';
        if ($target) {
            $ret .=' target="'.$target.'" ';
         }
        $ret .= 'onclick="'.$onclick.';editPopup.hide();">'.$title.'</a></p>';
        return $ret;
    }
    
    protected function createMenuSeparator() {
        return '<p class="menuseparator"/>';
    }
		
		/** bx_plugin::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
				$params = array();
				$params['pipelineName'] = 'xml';
				$params['xslt'] = 'admin.xsl';
				return $params;
		}
    
    public function getDataUri($path,$name,$ext) {
        return FALSE;
    }
    
    public function adminResourceExists($path, $id, $ext=null) {
        return $this; 
    }
}
?>
