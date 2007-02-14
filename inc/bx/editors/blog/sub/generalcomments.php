<?php

class bx_editors_blog_sub_generalcomments extends bx_editors_blog_sub {
    
    static protected $id = FALSE;
    static protected $parent;
    static protected $timezone = null;
        
    public function getInstance() {
        if (!self::$instance) {
            self::$instance = new bx_editors_blog_sub_generalcomments();
        }
        
        return self::$instance;
        
    }
    
    public function getEditContentById($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        
        if(isset($_GET['id'])) {
            return self::getEditComment($tablePrefix, $_GET['id']);
        } else {
            return self::getLatestComments($tablePrefix, $blogid);
        }
        /*
        $query = "SELECT * FROM ".$this->tablePrefix."comments AS generalcomments";
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
        
        
        if(self::$id == FALSE) 
            self::$id = isset($_GET['id']) ? $_GET['id'] : 0;
        return $this->getCommentData(self::$id);*/
    }

    public function handlePOST($path, $id, $data) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        if($data["deletecomments"]) {
            foreach($data["deletecomments"] as $name => $value) {
                if(isset($name) && $name != NULL) {
                    $this->deleteComment($name);
                }
            }
        }
        if(isset($data['comment_id'])) {
            $id = (int) $data['comment_id'];
            $quoted = $this->quotePostData($data);
            
            if($id != 0) {
                //$query = $this->getUpdateQuery('comments', $quoted, array('comment_author', 'comment_author_email','comment_author_url','comment_status', 'comment_content','comment_date','comment_author_ip'), $id);
                $query = "update ".$tablePrefix."comments set comment_author = '".$data['comment_author']."', comment_author_email='".$data['comment_author_email']."',comment_author_url='".$data['comment_author_url']."',comment_status='".$data['comment_status']."', comment_content='".$data['comment_content']."',comment_date='".$data['comment_date']."',comment_author_ip='".$data['comment_author_ip']."' where id = '".$id."'";
                $res = $this->dbwrite->query($query);
                if (MDB2::isError($res)) {
                    throw new PopoonDBException($res);
                }
            }
        }
        
    }
/*
    protected function getCommentData($id) {
        $query = "SELECT * FROM ".$this->tablePrefix."comments AS generalcomments WHERE id = '$id'";
        return bx_helpers_db2xml::getXMLByQuery($query, TRUE);
    }
    */
    static protected function deleteComment($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$tablePrefix."comments where id =".$id;
        
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
    }
    
    static protected function getEditComment($tablePrefix = null, $commentid) {
        $query = "select * from ".$tablePrefix."comments where id = ".$commentid;
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        $dom = new domDocument();
        
        $xml = '<comment>';
        $xml .= "<comment edit='true'>";
        $xml .= "<comment_author>".$row['comment_author']."</comment_author>";
        $xml .= "<comment_author_url>".$row['comment_author_url']."</comment_author_url>";
        $xml .= "<comment_author_email>".$row['comment_author_email']."</comment_author_email>";
        $xml .= "<comment_status>".$row['comment_status']."</comment_status>";
        $xml .= "<comment_content>".$row['comment_content']."</comment_content>";
        $xml .= "<comment_author_ip>".$row['comment_author_ip']."</comment_author_ip>";
        $xml .= "<comment_date>".$row['comment_date']."</comment_date>";
        $xml .= "<comment_id>".$row['id']."</comment_id>";
        $xml .= "</comment>";
        $xml .= '</comment>';
        $dom->loadXML($xml);
        
        return $dom;
    }
    
    static protected function getLatestComments($tablePrefix = null, $blogid) {
        bx_helpers_debug::webdump('here');
        
        $gmnow = gmdate("Y-m-d H:i:00",time() + 60);
        if (!$tablePrefix) {
            $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        }
         if (self::$timezone === NULL) {
            self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
        $perm = bx_permm::getInstance();
        //FIXME use real url
        if ($perm->isAllowed("/",array('ishashed','isuser'))) {
            $overviewPerm = 7;
        } else {
            $overviewPerm = 1;
        }
        
        $query = "select comments.id,
        DATE_FORMAT(comment_date,  '%Y-%m-%dT%H:%i:%SZ') as comment_date_iso,
        comment_content,  comment_author, comment_author_ip,
        date_add(comment_date, INTERVAL ". self::$timezone." SECOND) as comment_date,
        unix_timestamp(comment_date) as lastmodified,";
        if ($status > 1) {
            $query .= "comment_rejectreason,";
        }
       
        $query .= " comment_type from ".$tablePrefix."comments as comments";
        
        if ($overviewPerm != 7) {
            $query .= " and post_date < '".$gmnow."'";
        }
        
        $query .= " order by comment_date desc limit 10";
        bx_helpers_debug::webdump($query);
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        $dom = new domDocument();
        
        $xml = '<comments>';
        $xml .= self::getCommentXML($res);
        $xml .= '</comments>';
        
        $dom->loadXML($xml);
        
        return $dom;
    }
    
    static protected function getCommentXML($res) {
        $xml = '';
        $lastModified = 0;
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .= '<comment id="'.$row['id'].'" type="'.$row['comment_type'].'">';
            $xml .= '<content>'.htmlspecialchars(bx_helpers_string::makeLinksClickable($row['comment_content'])).'</content>';
            $xml .= '<author ip="'.$row['comment_author_ip'].'">'.htmlspecialchars($row['comment_author']).'</author>';
            if (isset($row['comment_date'])) {
                $xml .= '<date>'.$row['comment_date'].'</date>';
            }
            if (isset($row['unixtime'])) {
                $xml .= '<post_permauri>'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html</post_permauri>';
            }
            if(isset($row['post_uri'])) {
                $xml .= '<post_uri>'.$row['post_uri'].'</post_uri>';
            }
            if(isset($row['post_title'])) {
                $xml .= '<post_title>'.$row['post_title'].'</post_title>';
            }
            if(isset($row['comment_date_iso'])) {
                $xml .= '<date_iso>'.$row['comment_date_iso'].'</date_iso>';
            }
            if(isset($row['post_status'])) {
                $xml .= '<post_status>'.$row['post_status'].'</post_status>';
            }
            
            if(isset($row['comment_rejectreason'])) {
                $xml .= '<rejectreason>'.nl2br($row['comment_rejectreason']).'</rejectreason>';
            }
            $xml .= '</comment>';
            if (isset($row['lastmodified'])) {
                $lastModified = max($lastModified, $row['lastmodified']);
            }

        }

        if (self::$parent != NULL) {
            self::$parent->lastModified = max($lastModified,self::$parent->lastModified );
        }
        return $xml;
    }
    
}

