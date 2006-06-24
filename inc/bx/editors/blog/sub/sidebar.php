<?php

class bx_editors_blog_sub_sidebar extends bx_editors_blog_sub {
    
    
    public function getEditContentById($id) {
        
        $dom = new domdocument();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $dom->appendChild($dom->createElement("ajaxpost"));
            return $dom;
        }
        
        $dom->appendChild($dom->createElement("data"));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(0),true));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(1),true));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(2),true));
        
        
        return $dom;
    }
    
    public function handlePOST($path, $id, $data) {
        $db = $GLOBALS['POOL']->db;
        foreach($data['sidebar'] as $lid => $value) {
            $db->query("update ".$this->tablePrefix."sidebar set sidebar = -1 where sidebar = $lid");
            foreach ($value as $pos => $id) {
                $db->query("update ".$this->tablePrefix."sidebar set sidebar = $lid, position = $pos where id = $id");
            }
            
        }
        
    }
    
    
    protected function getCommentData($id) {
        $query = "SELECT * FROM ".$this->tablePrefix."sidebar AS sidebar WHERE sidebar = '$id' order by position";
        $data = bx_helpers_db2xml::getXMLByQuery($query, TRUE);
        $data->documentElement->firstChild->setAttribute("id",$id);
        return  $data->documentElement->firstChild;
        
    }
    
}

