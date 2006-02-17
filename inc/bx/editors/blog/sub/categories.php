<?php

class bx_editors_blog_sub_categories extends bx_editors_blog_sub {
    static protected $currentCategoryId = FALSE;
    
    public function getEditContentById($id) {
        $parts =  bx_collections::getCollectionAndFileParts($id, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        if (!$blogid) {
            $bogid = 1;
        }
        
        
        $del = isset($_GET['del']) ? (int) $_GET['del'] : FALSE;
        // execute delete before reading the categories from the db
        if($del !== FALSE && $del != 0) {
            $res = $this->dbwrite->query($this->getDeleteQuery('blogcategories', $del)." AND uri != 'root'");
            if($res) {
                bx_helpers_sql::updateCategoriesTree($blogid);
            }
        }

        $dom = $this->getCategoriesXML($blogid);
        $xp = new domxpath($dom);
        $res = $xp->query("/data/blogcategories/blogcategories");
        if ($res->length == 0) {
            
            $data['name'] = "All";
            $data['uri'] = "root";
            $data['fulluri'] = "root";
            $data['parentid'] = "0";
            $data['fullname'] = "root";
            $data['changed'] = "now()";
            $data['status'] = "1";
            $data['blog_id'] = $blogid;
            $quoted = $this->quotePostData($data);
            
            $query = $this->getInsertQuery('blogcategories', $quoted, array('name', 'uri', 'fulluri', 'parentid', 'fullname', 'changed', 'status', 'blog_id'));
            
            $this->dbwrite->query($query);
            $dom = $this->getCategoriesXML($blogid);
        }
        if(self::$currentCategoryId == FALSE) {
            self::$currentCategoryId = isset($_GET['id']) ? $_GET['id'] : 0;
        }
        $dom->documentElement->setAttribute('currentCategoryId', self::$currentCategoryId);
        
        return $dom;
    }

    public function handlePOST($path, $id, $data) {
        $parts =  bx_collections::getCollectionAndFileParts($id, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        if (!$blogid) {
            $bogid = 1;
        }
    
        if(isset($data['id'])) {
            $id = (int) $data['id'];

            $quoted = $this->quotePostData($data);
            $quoted['blog_id'] = $blogid;
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
                    $query = $this->getInsertQuery('blogcategories', $quoted, array('name', 'uri', 'parentid', 'blog_id'));
                }
                $id = $this->lastInsertId;
            }
            
            $res = $this->dbwrite->query($query);
            if($res) {
                bx_helpers_sql::updateCategoriesTree($blogid);
            }
                
            self::$currentCategoryId = $id;
        }
    }
    /*
    protected function updateCategoriesTree($blogid) {
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
        $rootQuery = "select id from ".$tablePrefix."blogcategories where parentid = 0 and blog_id = ".$blogid;
        
        $rootid = $this->dbwrite->queryOne($rootQuery);
        if (!$rootid) {
            print '<font color="red">You don\'t have a root collection, please define one</font><br/>
                    Otherwise the category output will not be correct<br/><br/>';
        } else {
            $tree->importTree($rootid,true,"name","","",(($blogid-1)*1000)+1);
        }    
    }*/
    
    
    protected function getCategoriesXML($blogid) {
        $query = "SELECT * FROM ".$this->tablePrefix."blogcategories AS blogcategories where blog_id = $blogid ORDER BY l";
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
    }
}

