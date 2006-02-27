<?php

class bx_editors_fck implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'fck');
    }
		
    public function getDisplayName() {
        return 'fck';
    }

}

?>
