<?php

class bx_editors_blog_sub_categories extends bx_editors_blog_sub {
    static protected $currentCategoryId = FALSE;
    
    public function getEditContentById($id) {
        $del = isset($_GET['del']) ? (int) $_GET['del'] : FALSE;
        // execute delete before reading the categories from the db
        if($del !== FALSE && $del != 0) {
            $res = $this->dbwrite->query($this->getDeleteQuery('blogcategories', $del)." AND uri != 'root'");
            if($res)
                $this->updateCategoriesTree();
        }

        $dom = $this->getCategoriesXML();
        
        if(self::$currentCategoryId == FALSE) {
            self::$currentCategoryId = isset($_GET['id']) ? $_GET['id'] : 0;
        }
        $dom->documentElement->setAttribute('currentCategoryId', self::$currentCategoryId);
        
        return $dom;
    }

    public function handlePOST($path, $id, $data) {

        if(isset($data['id'])) {
            $id = (int) $data['id'];

            $quoted = $this->quotePostData($data);
            if($id != 0) {
                $query = "UPDATE ".$this->tablePrefix."blogcategories ";
                if($data['parentidold'] == 0) {
                    $query.= "name=".$quoted['name'];
                } else {
                    $query = "UPDATE ".$this->tablePrefix."blogcategories SET name=".$quoted['name'].", uri=".$quoted['uri'].", parentid=".$quoted['parentid'];
                }
                $query.=" WHERE id=$id";
            } else {
                if($data['uri'] != '' && $data['name'] != '') {
                    $query = $this->getInsertQuery('blogcategories', $quoted, array('name', 'uri', 'parentid'));
                }
                $id = $this->lastInsertId;
            }
            
            $res = $this->dbwrite->query($query);
            if($res)
                $this->updateCategoriesTree();
                
            self::$currentCategoryId = $id;
        }
    }
    
    protected function updateCategoriesTree() {
        // this is the same code as in forms/blogcategories/updatetree.php
        $tree = new SQL_Tree($this->dbwrite);
        $tree->idField = "id";
        $tree->referenceField = "parentid";
        $tree->tablename = $this->tablePrefix."blogcategories";
        $tree->FullPath = "fulluri";
        $tree->FullTitlePath  = "fullname";
        $tree->Path = "uri";
        $tree->Title = "name";
        $tree->fullnameSeparator = " :: ";
        $data = array("name","uri","fulluri");
        
        $rootQuery = "select id from ".$this->tablePrefix."blogcategories where parentid = 0";
        
        $rootid = $this->dbwrite->queryOne($rootQuery);
        if (!$rootid) {
            print '<font color="red">You don\'t have a root collection, please define one</font><br/>
                    Otherwise the category output will not be correct<br/><br/>';
        } else {
            $tree->importTree($rootid,true,"name");
        }    
    }
    
    protected function getCategoriesXML() {
        $query = "SELECT * FROM ".$this->tablePrefix."blogcategories AS blogcategories ORDER BY l";
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
    }
}

