<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+

class bx_plugins_blog_trackback {
    
    static function getContentById($path,$id,$params, $p = null, $tablePrefix = "") {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return '<error/>';
        }
        
        $id = $params[0];
        
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        blogposts.post_title,
        blogposts.post_uri,
        users.user_login,
        unix_timestamp(blogposts.post_date) as unixtime,
        blogposts.post_comment_mode
        from '.$tablePrefix.'blogposts as blogposts left join '.$GLOBALS['POOL']->config->getTablePrefix().'users as users on blogposts.post_author = users.user_login
        where blogposts.id = "'.$id.'" ';
        
        
        //check if open for comments
        $res = $GLOBALS['POOL']->db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        if ($row['post_comment_mode'] == 99) {
            $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
        }
        
        $onemonthago = time() - 2678800; 
        if ($GLOBALS['POOL']->config->blogTrackbacksTimeLimit == 'true' && $onemonthago > $row['unixtime']) {
            return '<error/>';
        }
        
        if (!($row['post_comment_mode'] == 2 || ($row['post_comment_mode'] == 1 && $onemonthago < $row['unixtime']))) {
            return '<error/>';
        }
        
        
        $headers = "";
        $uri = bx_streams_blog::getUriById($params[0],$path);
        
        $emailBody = "";
        $data = popoon_classes_externalinput::removeMagicQuotes($_POST);
	$commentRejected = '';        

        if (isset($data['title'])) {
            $emailBody .=  "Title:    " . $data['title'] . "\n";
            $title = $data['title'];
        } else {
            $title = '';
        }


        if (isset($data['blog_name'])) {
            $emailBody .= "BlogName: " . $data['blog_name'] . "\n";
            $emailFrom = $data['blog_name']." <noadress@example.org>";
            $blogname = $data['blog_name'];
        } else {
            $blogname = '';
            $emailFrom = "unknown <noadress@example.org>";
	    $commentRejected .= "* No blogname given.\n";
        }

        if (isset($data['url'])) {
            $emailBody .= "URL:      " . $data['url'] . "\n";
            $url = $data['url'];
        } else {
            $url = '';
	    $commentRejected .= "* No URL given.\n";
        }

        $emailBody .= "IP:       " . $_SERVER['REMOTE_ADDR'] . "\n";

        if (isset($data['excerpt'])) {
            $emailBody .= "Excerpt:  " . strip_tags($data['excerpt']) . "\n";
            $excerpt = $data['excerpt'];
        }  else {
            $excerpt = '';
        }

        
        
        
        $emailBody .= "\nURI:\n ". BX_WEBROOT_W.$path.'archive/'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html'."\n";
        
        $emailSubject = "[".bx_helpers_config::getBlogName()."] ";

        $commentRejected .= bx_plugins_blog_spam::checkRBLs(array($url));
	$commentRejected .= bx_plugins_blog_spam::checkSenderIPBLs($_SERVER['REMOTE_ADDR']);
        if (preg_match("#<a[^>]+href=#",$data['title'])) {
		$commentRejected .= "* Links in title...\n";
	}
        if  (!$commentRejected) {
            $commentType = 2;
            $emailSubject .= "(Mod) ";
        } else {
            $commentType = 3;   
	    $emailSubject .= "(Rej) ";
	}        
        $emailSubject .= "New Trackback on '" . $row['post_title'] . "'";
        $db = $GLOBALS['POOL']->db;
        $query = 'insert into '.$tablePrefix.'blogcomments (
        comment_posts_id, 
        comment_author, 
        comment_author_url, 
        comment_author_ip,
        comment_date, 
        comment_content,
        comment_status,
        comment_type
        ) VALUES (
        '.$id.',
        '.$db->quote(htmlspecialchars($blogname),'text').',
        '.$db->quote($url).',
        "'.$_SERVER['REMOTE_ADDR'].'",
        "'.gmdate('c').'",
        '.$db->quote(htmlspecialchars(bx_helpers_string::utf2entities($excerpt))).',
        '.$commentType.',
        "TRACKBACK"
        )';
        
         
        $err = $db->query($query);
        
        $db->loadModule('extended'); 
        $lastID = $db->getAfterID(null,$tablePrefix.'blogcomments');       
         
        $emailBody .= "Edit URI:\n ".  BX_WEBROOT.'admin/?edit=/forms/blogcomments/?id='.$lastID ."\n";

        if ($GLOBALS['POOL']->config->lastdbversion >= 5266) {
            $hash = md5($lastID . rand().microtime(true));
            $hashPrefix = "a";
            $query = 'update '.$tablePrefix.'blogcomments set comment_hash = ' . $GLOBALS['POOL']->db->quote($hashPrefix . $hash) . ' where id = ' . $lastID; 
            $GLOBALS['POOL']->dbwrite->query($query);
            $emailBody .= "Moderation URI (Click the link to accept this trackback) :\n ";
            $emailBody .= BX_WEBROOT.'admin/webinc/approval/?hash='.$hashPrefix.$hash."\n";  
        } 
        
        if   ($commentRejected) {
            
            $emailBody .= "\n" . $commentRejected ."\n";
        }
        if ($GLOBALS['POOL']->config->blogSendRejectedCommentNotification == "true" || $commentType < 3) {
            bx_notificationmanager::sendToDefault($row['user_login'],$emailSubject, $emailBody, $emailFrom);
        }
        return '<ok/>';
        
    }
}
?>
