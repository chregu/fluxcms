<?php

class bx_metadatas_text_tags extends bx_metadatas_text_textfield {
    
    
    
    function getPropertyValueFromPOSTValue($value,$res) {
      
        bx_metaindex::setTags($res->getId(),$value);
        return $value;
    }
    

    public function serializeToDOM() {
        $dom = new domDocument();
        
        $textField = $dom->createElement('metadata');
        $textField->setAttribute('type', 'textfield');
        $textField->setAttribute('size', $this->size);
        $textField->setAttribute('maxLength', $this->maxLength);    
            
        
        if (!empty($this->table)) {
            $db = $GLOBALS['POOL']->db;
            $prefx = $GLOBALS['POOL']->config->getTablePrefix();
            if ($db) {
                $tq = "SELECT ".$this->idfield.",".$this->namefield.",".$this->displayfield." FROM ".$prefx. $this->table;
                $tq.= " WHERE ".$this->namefield."!='' ORDER BY ".$this->order;
                
                $res = $db->query($tq);
                if (!MDB2::isError($res)) {
                    $tags = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
                    
                    if (sizeof($tags) > 0) {
                        $tagsNode = $dom->createElement('tags');    
                        foreach($tags as $tag) {
                            $tagNode = $dom->createElement('tag');
                            $tagNode->setAttribute($this->namefield, $tag[$this->namefield]);
                            $tagNode->setAttribute($this->idfield, $tag[$this->idfield]);
                            $tagNode->setAttribute('displayname', $tag['displayname']);        
                            $tagsNode->appendChild($tagNode);
                        
                        }
                        
                        $textField->appendChild($tagsNode);
                    }
                    
                } else {
                    //bx_log::log($res->getUserinfo());
                }
            }
        }
        
        
        
        
        return $textField;
    }
}

?>
