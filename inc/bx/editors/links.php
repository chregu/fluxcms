<?php

class bx_editors_links extends bx_editor implements bxIeditor {    
    /**
    The table names
    */
    public $linksTable = "bloglinks";
    public $categoryTable = "bloglinkscategories";
    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'links');
    }
    
    public function getDisplayName() {
        return "Links";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
        
        // set tags
        $tags = $data['tags'];
        
        // fix date
        if (isset($data['date'])) {
            $data['date'] = $this->fixDate($data['date']);
        }
        
        //if id is set, do an update or delete
        if (isset($data['id']) && $data['id']) {
            if (isset($_GET['delete']) && $_GET['delete'] == 1) {
                $query = "delete from ".$tablePrefix.$this->linksTable ." where id = " . $db->quote($data['id']);
                $db->query($query);
                
                bx_metaindex::removeAllTagsById($id,true);
                header("Location: ./");
                
            } else {
                
                bx_metaindex::setTags($id,$tags,true);
                unset($data['tags']);
                $data['rang'] = $this->updateRang((int) $data['id'],(int) $data['rang']);
                
                $query = "update ".$tablePrefix.$this->linksTable ." set changed = now()";
                foreach ($data as $key => $value) {
                    $query .= ", " . $key . " = " . $db->quote($value);
                }
                
                $query  .= " where id = " . $db->quote($data['id']);
                $db->query($query);
            }
        }
        // else an insert
        else {
            $data['id'] =  $GLOBALS['POOL']->dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
            $id = dirname($id)."/".$data['id'].".links";
            bx_metaindex::setTags($id,$tags,true);
            unset($data['tags']);
            
            $data['rang']++;
            
            $query = "insert into ".$tablePrefix.$this->linksTable ." (changed," . implode(",",array_keys($data)) .") VALUES ( now()";
            foreach ($data as $key => $value) {
                $query .= ",". $db->quote($value);
            }
            $query .= ")";
            $res = $db->query($query);
            if ($db->isError($res)) {
                throw new PopoonDBException($res);
            }
            $this->updateRang((int) $data['id'],(int) $data['rang']);
            
            header("Location: ". BX_WEBROOT . $path.$id);
        }
        
        
        
    }
    
    public function getEditContentById($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        $db2xml = new XML_db2xml($db,"links","Extended");
        
        
        
        $parts = bx_collections::getCollectionUriAndFileParts($id);
        
        
        
        $query = "select categories.id, categories.name from ".$tablePrefix.$this->categoryTable." as categories";
        $db2xml->add($query);
        $query = "select links.id, links.text, links.rang from ".$tablePrefix.$this->linksTable." as links";
        $db2xml->add($query);
        
        if ($parts['name']) {
            $query = "select * from ".$tablePrefix.$this->linksTable." as editlink where id = " . (int) $parts['name'];
            $db2xml->add($query);
        } else {
            //do an empty one
            $query = "show columns from ".$tablePrefix.$this->linksTable;
            $_cols = $db->queryCol($query);
            foreach ($_cols as $value) {
                $cols[$value] = "";
            }
            $db2xml->add(array("editlink" => $cols));
            
        }
       
        $db2xml->add(array("tags" => implode(" ",bx_metaindex::getTagsById($id))));
        return $db2xml->getXMLObject();
        
        
    }
    
     
    
    protected function updateRang($id, $rang) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        $res = $db->queryOne("select rang from ".$tablePrefix.$this->linksTable." where rang = ".$rang. " and id != ". $id);
        if ($res || $rang == 0) {
            if ($id) {
                $moveby = 1;
                $add = " and id != " . $id;
            } else {
                $moveby = 1 ;
                $add = "";
            }
            $GLOBALS['POOL']->dbwrite->query("update ".$tablePrefix.$this->linksTable." set rang = rang + $moveby where rang > ". $rang. " $add");
            return $rang+ 1;
        } else {
            return $rang;
        }
    }
    
    protected function  fixDate($date) {
        if (!$date || $date == "now()") {
            return date("Y-m-d H:i:s",time());
        }
        $date =  preg_replace("/([0-9])T([0-9])/","$1 $2",$date);
        $date =  preg_replace("/([\+\-][0-9]{2}):([0-9]{2})/","$1$2",$date);
        $date = strtotime($date);
        if ($date <= 0) {
            return  date("Y-m-d H:i:s",time());
        }
        return  date("Y-m-d H:i:s",$date);
    }
    
   
}

?>
