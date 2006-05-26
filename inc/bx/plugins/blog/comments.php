<?php
/*
 * Created on Dec 30, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class bx_plugins_blog_comments {
    static protected $parent;
    static protected $timezone = null;

    static function getContentById($path,$id,$params,$parent=Null,$tablePrefix = "") {
        
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $blogid =  $p->getParameter($colluri,"blogid");
        if (!$blogid) {$blogid = 1;};
        self::$parent = $parent;
        if (!isset($params[1])) {
            $params[1] = 1;
        }
        if(strpos($params[0], 'latest') !== FALSE) {
            return self::getLatestComments($params[1], $tablePrefix, $blogid);
        } else {
            return self::getPostComments($params[0],1, $tablePrefix, $blogid);
        }
    }
    
    protected function getCommentXML($res) {
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
    
    
    protected function getLatestComments($status = 1, $tablePrefix = null, $blogid) {
        
        $gmnow = gmdate("Y-m-d H:i:00",time() + 60);
        if (!$tablePrefix) {
            $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        }
         if (self::$timezone === NULL) {
            self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
        $perm = bx_permm::getInstance();
        if ($perm->isLoggedIn()) {
            $overviewPerm = 7;
        } else {
            $overviewPerm = 1;
        }
        $query = "select blogcomments.id,
        DATE_FORMAT(comment_date,  '%Y-%m-%dT%H:%i:%SZ') as comment_date_iso,
        post_title, post_uri, comment_content,  comment_author, comment_author_ip,
        date_add(comment_date, INTERVAL ". self::$timezone." SECOND) as comment_date,
        unix_timestamp(post_date) as unixtime,
        unix_timestamp(blogposts.changed) as lastmodified,";
        if ($status > 1) {
            $query .= "comment_rejectreason,";
        }
       
        $query .= " comment_type from ".$tablePrefix."blogcomments as blogcomments
        left join ".$tablePrefix."blogposts as blogposts on blogposts.id = blogcomments.comment_posts_id where blogposts.post_status & ".$overviewPerm . " and blogcomments.comment_status = $status and blogposts.blog_id = $blogid ";
        
        if ($overviewPerm != 7) {
            $query .= " and post_date < '".$gmnow."'";
        }
        
        $query .= "order by comment_date desc limit 10";
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        return '<comments>'.self::getCommentXML($res).'</comments>';
    }

    protected function getPostComments($id, $status = 1, $tablePrefix = null, $blogid) {
        
        if (!$tablePrefix) {
            $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        }
        if (self::$timezone === NULL) {
            self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
        $res = $GLOBALS['POOL']->db->query("select id,
                    comment_content,
                    comment_author,
                    comment_author_ip,
                    date_add(comment_date, INTERVAL ". self::$timezone." SECOND),
                    comment_type from ".$tablePrefix."blogcomments
                    where comment_posts_id = $id and comment_status = $status  order by comment_date desc limit 10");
        if (MDB2::isError($res)) {
                    throw new PopoonDBException($res);
        }
        
        return '<comments>'.self::getCommentXML($res).'</comments>';
    }
}
?>
