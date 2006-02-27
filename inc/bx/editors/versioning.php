<?php

class bx_editors_versioning extends bx_editor implements bxIeditor {    

		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'versioning');
    }
	
    public function getDisplayName() {
        return "Versioning";
    }
    
        
    public function handlePOST($path, $id, $data) {
        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
        return $parts['coll']->handlePostById($parts['rawname'],$data,"FullXML");
    }
    
    public function getEditContentById($id) {
        
        
        //echo "gecbi ".$id;
    }

}

?>
