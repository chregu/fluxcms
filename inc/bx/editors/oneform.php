<?php

class bx_editors_oneform extends bx_editor implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'oneform');
    }

    public function getDisplayName() {
        return "One Form";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
     $url = substr($id, 0, strrpos($id, '/', -2)+1);
     $perm = bx_permm::getInstance();
     if($perm->isAllowed($url, array('collection-back-edit_file'))) {
     
	        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
	       return $parts['coll']->handlePostById($parts['rawname'],$data,"FullXML");
     }
    }
    
}

?>
