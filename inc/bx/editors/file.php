<?php

class bx_editors_file extends bx_editor implements bxIeditor {    
    
    public function getPipelineName() {
        return "file";
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
