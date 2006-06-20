<?php

class bx_editors_assets extends bx_editor implements bxIeditor {
    
    public function __construct() {
    }
     
    public function getDisplayName() {
        return 'Assets Editor';
    }
    
    public function getPipelineParametersById($path, $id) {
		return array('pipelineName'=>'assets');
    }   
    
    public function getEditContentById($id) {
        $content = new DomDocument();
        if ($content instanceof DOMDocument) {
            $content->load('<assets/>');    
        }
    
        
        return $content; 
    } 
}




?>
