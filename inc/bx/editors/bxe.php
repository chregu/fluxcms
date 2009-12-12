<?php

class bx_editors_bxe implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'bxe');
    }
    
    public function getDisplayName() {
        return "BXE";
    }

}

?>
