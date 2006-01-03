<?php

class bx_plugins_blog_commentsnotification{
    
    
    static function sendNotificationMails($commentId,$postId,$collUri) {
        $parts =  bx_collections::getCollectionAndFileParts($collUri, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        $tablePrefix = $tablePrefix.$p->getParameter($collUri,"tableprefix");
        
        $query= "Select comment_author, comment_author_email,  comment_notification_hash from ".$tablePrefix."blogcomments where comment_posts_id = ".$postId
        ." and id != ".$commentId." and comment_notification = 1 and comment_author_email != ''";
        
        $res = $GLOBALS['POOL']->db->query($query);
        
        
        $mail_query_post="Select post_uri, post_title from ".$tablePrefix."blogposts where id = $postId";
        $mail_res_post = $GLOBALS['POOL']->db->query($mail_query_post);
        $mail_re_post = $mail_res_post->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        $comment_query="Select id, comment_author, comment_author_email, comment_author_ip, comment_content
        from ".$tablePrefix."blogcomments where comment_posts_id = ".$postId
        ." and id = ".$commentId;
        $comment_res = $GLOBALS['POOL']->db->query($comment_query);
        $comment_re = $comment_res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        $mailSent = array();
        $mailSent[] = $comment_re['comment_author_email'];
        
        while ($re = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) { 
            if (in_array($re['comment_author_email'],$mailSent)) {
                continue;
            }
            $emailBody = "Dear ".$re['comment_author']."

There is a new comment from " .$comment_re['comment_author']." to the post \"" .$mail_re_post['post_title']."\"

URL: ". BX_WEBROOT_W. $collUri. $p->getNewPermaLink($mail_re_post['post_uri']) ."#comment".$comment_re['id']."

Content:
".str_replace('<br />',"\n",html_entity_decode($comment_re['comment_content'],ENT_NOQUOTES,"UTF-8"))."

If you don't want to reveive mails for this post anymore click the link below
". BX_WEBROOT."webinc/php/commentdeactivate.php?id=".$re['comment_notification_hash'];
            $emailSubject = "[".bx_helpers_config::getBlogName()."] New Comment on Post ".$mail_re_post['post_title'];
            $emailFrom = "mailer@flux-cms.org";
            $emailTo = $re['comment_author_email'];
            $mailSent[] = $re['comment_author_email'];
            $n = bx_notificationmanager::getInstance("mail");
            $n->send($emailTo,$emailSubject, $emailBody,$emailFrom);
            
            
        }   
    }
}
?>
