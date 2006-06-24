<?php

class bx_editors_blog_sub_sidebar extends bx_editors_blog_sub {
    
    protected $newEntry= null;
    
    
    public function getInstance() {
        if (!self::$instance) {
            self::$instance = new bx_editors_blog_sub_sidebar();
        }
        
        return self::$instance;
        
    }

    public function __construct() {
        parent::__construct();
    }
    public function getEditContentById($id) {
        
        $dom = new domdocument();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (strpos($id,"sub/sidebar/listupdate/") !== false) {
                $dom->appendChild($dom->createElement("ajaxpost"));
                return $dom;
            } else if (strpos($id,"sub/sidebar/edit/") !== false)  {
                $root = $dom->appendChild($dom->createElement("edit"));
                $json = bx_helpers_json::encode($this->getEditData((int) $_POST['id']));
                $root->appendChild($dom->createTextNode($json));
                return $dom;
                
            } else if (strpos($id,"sub/sidebar/sendedit/") !== false)  {
                $root = $dom->appendChild($dom->createElement("sendedit"));
                if ($this->newEntry) {
                    $json = bx_helpers_json::encode($this->newEntry);
                    
                    $root->appendChild($dom->createTextNode($json));
                    
                }
                return $dom;
            }  else if (strpos($id,"sub/sidebar/delete/") !== false)  {
                $root = $dom->appendChild($dom->createElement("delete"));
                if ($this->newEntry) {
                    $json = bx_helpers_json::encode($this->newEntry);
                    
                    $root->appendChild($dom->createTextNode($json));
                    
                }
                return $dom;
            }
            
        }
        
        $dom->appendChild($dom->createElement("data"));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(0),true));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(1),true));
        $dom->documentElement->appendChild($dom->importNode( $this->getCommentData(2),true));
        
        
        return $dom;
    }
    
    public function handlePOST($path, $id, $data) {
        $db = $GLOBALS['POOL']->dbwrite;
        
        if (isset($data['sitebar'])) {
            foreach($data['sidebar'] as $lid => $value) {
                // $db->query("update ".$this->tablePrefix."sidebar set sidebar = 0 where sidebar = $lid");
                foreach ($value as $pos => $id) {
                    $db->query("update ".$this->tablePrefix."sidebar set sidebar = " .(int) $lid. ", position = ".(int) $pos."  where id = ". (int) $id);
                }
                
            }
        } else if (isset($data['edit'])) {
            $quoted = $this->quotePostData($data['edit']);
            if (!empty($data['edit']['id'])) {
                
                $query = $this->getUpdateQuery('sidebar', $quoted, array('name', 'content'), $data['edit']['id']);
                $id = $data['edit']['id'];
                
            } else {
                $id = $db->nextID($this->tablePrefix."_sequences");
                $pos = $db->queryOne("select max(position) from ".$this->tablePrefix."sidebar where sidebar = 0");
                $quoted['position'] = $pos + 1;
                $query = $this->getInsertQuery('sidebar', $quoted, array('name', 'content','position'),$id);
                
            }
            $this->newEntry = array('id'=>$id,'content'=>'
                        <a href="#editItem'.$id.'" onclick="return editItem('.$id.')" style="float: right">[...]</a>
                        '. $data['edit']['name']
                    
                );
            
            $db->query($query);
        } else if (!empty($data['delete'])) {
            $db->query("delete from ".$this->tablePrefix."sidebar where id = ".$db->quote($data['delete']));
            $this->newEntry = array("id" => $data['delete']);
        }
        
    }
    
    
    protected function getCommentData($id) {
        $query = "SELECT * FROM ".$this->tablePrefix."sidebar AS sidebar WHERE sidebar = '$id' order by position";
        $data = bx_helpers_db2xml::getXMLByQuery($query, TRUE);
        $data->documentElement->firstChild->setAttribute("id",$id);
        return  $data->documentElement->firstChild;
        
    }
    
    
    protected function getEditData($id) {
        $query = "SELECT * FROM ".$this->tablePrefix."sidebar AS item WHERE id = '$id' ";
        $res = $GLOBALS['POOL']->db->query($query);
        return $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        return null;
        //return  $data->documentElement->firstChild;
        
    }
    
}

