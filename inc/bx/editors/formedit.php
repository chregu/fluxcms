<?php

class bx_editors_formedit extends bx_editor implements bxIeditor {    
    
    public function getPipelineName() {
        return "formedit";
    }
    
    public function getDisplayName() {
        return "Form Editor (not implemented yet)";
    }
    
    public function handlePOST($path, $id, $data) {
        $newdata = array();
        //fix xpath links
        foreach ($data as $name => $value) {
            $name = str_replace(array("{","}"),array("[","]"),$name);
            $newdata[$name] = $value;
        }
        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
         $parts['coll']->handlePostById($parts['rawname'],$newdata,"XPathInsert");
        
            
    }
    
}

?>
