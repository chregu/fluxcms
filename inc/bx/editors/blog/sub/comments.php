<?php

class bx_editors_blog_sub_comments extends bx_editors_blog_sub {
    
    static protected $id = FALSE;
    
    public function getEditContentById($id) {
        if(self::$id == FALSE) 
            self::$id = isset($_GET['id']) ? $_GET['id'] : 0;
            
        return $this->getCommentData(self::$id);
    }

    public function handlePOST($path, $id, $data) {
        if(isset($data['id'])) {
            $id = (int) $data['id'];
            $quoted = $this->quotePostData($data);
            
            if($id != 0) {
                $query = $this->getUpdateQuery('blogcomments', $quoted, array('comment_author', 'comment_author_email','comment_author_url','comment_status', 'comment_content','comment_date','comment_author_ip'), $id);
            }
            $res = $this->dbwrite->query($query);
            self::$id = $id;
        }
        
    }

    protected function getCommentData($id) {
        $query = "SELECT * FROM ".$this->tablePrefix."blogcomments AS blogcomments WHERE id = '$id'";
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
    }
    
}

