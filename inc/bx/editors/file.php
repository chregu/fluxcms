<?php

class bx_editors_file extends bx_editor implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'file');
    }
    
    public function getDisplayName() {
        return "File";
    }
    
    public function handlePost($path, $id, $data) {
        if(!empty($_FILES)) {
                $this->uploadFile($path, $id, $_FILES);
        }
    }
    
    
}

?>
