<?php

class bx_editors_kupu implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'kupu');
    }

    public function getDisplayName() {
        return "kupu";
    }

}

?>
