<?php

class bx_editors_blog_sub_blogroll extends bx_editors_blog_sub {
    static protected $currentCategoryId = FALSE;
    static protected $currentLinkId = FALSE;
    
    public function getEditContentById($id) {
        $parts =  bx_collections::getCollectionAndFileParts($id, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        if (!$blogid) {
            $bogid = 1;
        }
        $catdel = isset($_GET['catdel']) ? (int) $_GET['catdel'] : FALSE;
        $linkdel = isset($_GET['linkdel']) ? (int) $_GET['linkdel'] : FALSE;
        
        if($catdel !== FALSE) {
            $this->dbwrite->query($this->getDeleteQuery('bloglinkscategories', $catdel));
        } else if($linkdel !== FALSE) {
            $this->dbwrite->query($this->getDeleteQuery('bloglinks', $linkdel));
        }
        
        $dom = $this->getBlogrollXML($blogid);
        
        if(self::$currentCategoryId == FALSE) {
            self::$currentCategoryId = isset($_GET['category']) ? $_GET['category'] : 0;
        }
        $dom->documentElement->setAttribute('currentCategoryId', self::$currentCategoryId);

        if(self::$currentLinkId == FALSE) {
            self::$currentLinkId = isset($_GET['link']) ? $_GET['link'] : 0;
        }
        $dom->documentElement->setAttribute('currentLinkId', self::$currentLinkId);
        
        return $dom;
    }

    public function handlePOST($path, $id, $data) {
        $parts =  bx_collections::getCollectionAndFileParts($id, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        
        if(isset($data['category'])) {
            $data = $data['category'];
            $id = (int) $data['id'];
            $quoted = $this->quotePostData($data);
            $quoted['blog_id'] = $blogid;
            $quoted['changed'] = 'now()';
            if($id != 0) {
                $query = $this->getUpdateQuery('bloglinkscategories', $quoted, array('name', 'rang','changed','blog_id'), $id);
            } else {
                $query = $this->getInsertQuery('bloglinkscategories', $quoted, array('name', 'rang','changed','blog_id'));
                $id = $this->lastInsertId;
            }
            $res = $this->dbwrite->query($query);
            self::$currentCategoryId = $id;
        
        } else if(isset($data['link'])) {
            $data = $data['link'];
            $id = (int) $data['id'];
            $quoted = $this->quotePostData($data);
            $quoted['blog_id'] = $blogid;
            $quoted['changed'] = 'now()';
            
            if($id != 0) {
                $query = $this->getUpdateQuery('bloglinks', $quoted, array('bloglinkscategories', 'text', 'link','rang','changed','blog_id'), $id);
            } else {
                $query = $this->getInsertQuery('bloglinks', $quoted, array('bloglinkscategories', 'text', 'link','rang','changed','blog_id'));
                $id = $this->lastInsertId;
            }
            $res = $this->dbwrite->query($query);
            self::$currentLinkId = $id;
        }
    }
    
    protected function getBlogrollXML($blogid) {
        $tp = $this->tablePrefix;
        $query = "SELECT * FROM ".$tp."bloglinkscategories AS bloglinkscategories "; 
        $query.= "LEFT JOIN ".$tp."bloglinks AS bloglinks ON bloglinks.bloglinkscategories = bloglinkscategories.id ";
        $query.= "WHERE bloglinkscategories.blog_id = ".$blogid;
        $query.= " ORDER BY bloglinkscategories.rang, bloglinks.rang";
        
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
    }
    
    
}


