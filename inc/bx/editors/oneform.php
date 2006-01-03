<?php

class bx_editors_oneform extends bx_editor implements bxIeditor {    
    
    public function getPipelineName() {
        return "oneform";
    }
    
    public function getDisplayName() {
        return "One Form";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
       return $parts['coll']->handlePostById($parts['rawname'],$data,"FullXML");
    }
    
}

?>
