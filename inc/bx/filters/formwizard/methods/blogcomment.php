<?php

class bxfw_blogcomment {
    
    private $allowedTags = array('b','i','a','ul','li','ol','pre','blockquote','br','p');
    
    private $knownspammers = array();
    
    private $tidyOptions = array(
    "output-xhtml" => true,
    "show-body-only" => true,
    
    "clean" => true,
    "wrap" => "350",
    "indent" => true,
    "indent-spaces" => 1,
    "ascii-chars" => false,
    "wrap-attributes" => false,
    "alt-text" => "",
    "doctype" => "loose",
    "numeric-entities" => true,
    "drop-proprietary-attributes" => true
    );
    public function __construct($fw) {
        $this->parent = $fw;
    }
    
    function emailFields($emailBodyID = '') {
        // not needed anymore;
        return true;
        
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        
        $fields = $this->parent->getFields();
        
        $isok = false;
        //add some more fields and clean some others
        $fields['remote_ip'] = $_SERVER['REMOTE_ADDR'];
        $fields['name'] = strip_tags($fields['name'] );
        $fields['email'] = strip_tags($fields['email'] );
        $fields['base'] = strip_tags($fields['base'] );
        foreach($fields as $name => $value) {
            $fields[$name] = bx_helpers_string::utf2entities(str_replace("&","&amp;",trim($value)));
        }
/*

FIXME: can't set cookies, due to the location redirect at the end...
if (isset($fields['comment_remember'])) {
            $remember = array('name' => $fields['name'],'email' => $fields['email'],'base' => $fields['base'],'comment_notify' => @$fields['comment_notify'],'comment_remember' => $fields['comment_remember']);
            setcookie("blog_remember", serialize($remember), 3600*24*60,"/");
        } else if (isset($_COOKIE['blog_remember'])) {
            setcookie("blog_remember", null);
        }
   */     
        
        //get TablePrefix
        $parts =  bx_collections::getCollectionAndFileParts($this->parent->collUri, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        
        $blogTablePrefix = $tablePrefix.$p->getParameter($parts['coll']->uri,"tableprefix");
        
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        blogposts.post_title,
        blogposts.post_uri,
        users.user_login,
        unix_timestamp(blogposts.post_date) as unixtime,
        blogposts.post_comment_mode
        
        from '.$blogTablePrefix.'blogposts as blogposts left join '.$tablePrefix.'users as users on blogposts.post_author = users.user_login
        where blogposts.id = "'.$fields['id'].'" ';
        
        $res = $GLOBALS['POOL']->db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        if(isset($fields['captcha'])) {
            if (!$this->checkCaptcha($fields['captcha'], $fields['imgid'])) {
                return false;
            }
        }
        
        
        if ($row['post_comment_mode'] == 99) {
            $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
        }
        if (!($row['post_comment_mode'] == 2 || ($row['post_comment_mode'] == 1 && (time() - 2678800) < $row['unixtime']))) {
            die("No comments allowed anymore...");
        }
        
        /* flood-protection */
        /*$query = "SELECT unix_timestamp(comment_date)  FROM ".$blogTablePrefix."blogcomments WHERE comment_author_IP='".$_SERVER['REMOTE_ADDR']."' ORDER BY comment_date DESC LIMIT 1";
        
        $res = $GLOBALS['POOL']->db->query($query);
        $time_lastcomment = $res->fetchOne(0);
        if (time()  - $time_lastcomment) < 60){
            die ("Flood protection! You're not allowed to post comments within that short of a timespan");
        } */
        /* end flood-protection */
        
        
        $fields['uri'] = BX_WEBROOT_W.$parts['coll']->uri.'archive/'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
        
        $screenNode = $this->parent->confctxt->query("/bxco:wizard/bxco:screen[@emailTo]");
        $screenNode = $screenNode->item(0);
        // clean up comment
        if (class_exists('tidy')) {
            $tidy = new tidy();
            if(!$tidy) {
                throw new Exception("Something went wrong with tidy initialisation. Maybe you didn't enable ext/tidy in your PHP installation. Either install it or remove the tidy transformer from your sitemap.xml");
            }
        } else {
            $tidy = false;
        }
        
        // this preg escapes all not allowed tags...
        $_tags = implode("|",$this->allowedTags).")])#i";
        $fields['comments'] = preg_replace("#\<(/[^(".$_tags,"&lt;$1", $fields['comments']);
        $fields['comments'] = preg_replace("#\<([^(/|".$_tags,"&lt;$1", $fields['comments']);
        
        $allowedTagsString = "<".implode("><",$this->allowedTags).">";
        if ($tidy) {
            $tidy->parseString(strip_tags(nl2br($fields['comments']),$allowedTagsString ),$this->tidyOptions,"utf8");
            $tidy->cleanRepair();
            $fields['comments'] = popoon_classes_externalinput::basicClean((string) $tidy);
            // and tidy it again 
            $tidy->parseString($fields['comments']);
            $tidy->cleanRepair();
            $fields['comments'] = (string) $tidy;
        } else {
            $fields['comments'] =  popoon_classes_externalinput::basicClean(strip_tags(nl2br($fields['comments']),$allowedTagsString));
        }
        $commentRejected = "";
        
        /* known spammer user */
        $simplecache = popoon_helpers_simplecache::getInstance();
        $simplecache->cacheDir = BX_TEMP_DIR;
        $deleteIt = false;
        
        //check for pineapleproxy
        if (isset($_SERVER['HTTP_VIA']) && stripos($_SERVER['HTTP_VIA'],'pinappleproxy') !== false) {
            $commentRejected .= "* Uses known spammer proxy: ". $_SERVER['HTTP_VIA'] . "\n";
        }
        
        //get latest spammer name list every 6 hours
        /*
        $this->knownspammers = $simplecache->simpleCacheRemoteArrayRead("http://www.bitflux.org/download/antispam/knownspammer.dat",21600);
        
        if (in_array(strtolower(preg_replace("#[^a-z]#i","",$fields['name'])),$this->knownspammers)) {
            $commentRejected .= "* Known spammer name: " . $fields['name'] ."\n";
            $deleteIt = true;
        }*/
        
        
        /* If url field is filled in, it was a bot ...*/
        if (isset($fields['url']) && $fields['url'] != "") {
            $commentRejected .= "* URL field was not empty, assuming bot: " . $fields['url']."\n";        
            $deleteIt = true;
        }
        /* Max 5 links per post and SURBL check */
        if (preg_match_all("#http://[\/\w\.\-]+#",$fields['comments'], $matches) || $fields['base'] != '') {
            if ($fields['base'] != '') {
                $matches[0][] = $fields['base'] ;
            }
            if (isset($matches[0])) {
                $urls = array_unique($matches[0]);
                if ( count($urls) > 5) {
                    $commentRejected .= "* More than 5 unique links in comment (".count($urls) .")\n";
                    if (count($urls) > 10) {
                        $deleteIt = true;
                    }
                }
                
                $commentRejected .= bx_plugins_blog_spam::checkRBLs($urls);
            }
        }
        
        //check sender IP against xbl.spamhaus.org
        $xblcheck = bx_plugins_blog_spam::checkSenderIPBLs($_SERVER['REMOTE_ADDR']);
        
        if (!$commentRejected) {
            // insert comment
            $comment_status = 1;
        } else if ($deleteIt) {
            $comment_status = 3;
        } else {
            $comment_status = 2;
        }
        //delete all rejected comments older than 3 days...
        $query = 'delete from '.$blogTablePrefix.'blogcomments where comment_status = 3 and now() - comment_date > 3600 * 24 * 3';
        $res = $GLOBALS['POOL']->dbwrite->query($query);

        //delete all moderated comments older than 14 days...
        $query = 'delete from '.$blogTablePrefix.'blogcomments where comment_status = 2 and now() - comment_date > 3600 * 24 * 14';
        $res = $GLOBALS['POOL']->dbwrite->query($query);        
        
        $emailFrom = str_replace(":"," ",html_entity_decode($fields['name'],ENT_QUOTES,'ISO-8859-1'));
        
        if ($fields['email']) {
            $emailFrom .= ' <'.html_entity_decode($fields['email'],ENT_QUOTES,'ISO-8859-1').'>';
        } else {
            $emailFrom .= ' <unknown@example.org>';
        }
        // check if emailFrom is a valid input. if not -> reject!!!
        if(strpos($emailFrom, "\n") !== FALSE or strpos($emailFrom, "\r") !== FALSE) { 
            print ("Comment rejected. Looks like you're trying to spam the world....");
            die();
        }
        $comment_notification_hash = md5($fields['email'] . rand().microtime(true));
        $db = $GLOBALS['POOL']->dbwrite;
        if (!isset($fields['comment_notification'])) {
            $fields['comment_notification'] = 0;
        }
        $query = 'insert into '.$blogTablePrefix.'blogcomments (comment_posts_id, comment_author, comment_author_email, comment_author_ip,
        comment_date, comment_content,comment_status, comment_notification, comment_notification_hash,
        comment_author_url         
        ) VALUES ("'.$row['id'].'",'.$db->quote($fields['name'])
        .','.$db->quote($fields['email'],'text').','.$db->quote($fields['remote_ip']).',"'.gmdate('c').'",'.$db->quote(bx_helpers_string::utf2entities($fields['comments'])).','.$comment_status.','.$db->quote($fields['comment_notification']).',"'.$comment_notification_hash.'",'.$db->quote($fields['base'],'text').')';
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        
        $GLOBALS['POOL']->dbwrite->loadModule('Extended'); 
        $lastID = $GLOBALS['POOL']->dbwrite->getAfterID(null,$blogTablePrefix.'blogcomments');
        $fields['edituri'] = BX_WEBROOT.'admin/?edit=/forms/blogcomments/?id='.$lastID;
        $fields['uri'] .= '#comment'.$lastID;
        //get email et al
        $emailTo = $row['user_login'];
        //if ($row['user_email'] && !$deleteIt) {
                $emailSubject = '['.bx_helpers_config::getBlogName().'] ' ;
                if ($commentRejected) {
                    $hashPrefix = "a";
                    if ($deleteIt) {
                        $emailSubject .= "(Rej) ";
                    } else {
                        $emailSubject .= "(Mod) ";
                    }
                    $fields['accepturi'] = "(Click the link to accept this comment [1]):\n";
                } else {
                    $hashPrefix = "r";
                    $fields['accepturi'] = "(Click the link to reject this comment [1]) :\n";
                }
                // insert hash
                if ($GLOBALS['POOL']->config->lastdbversion >= 5266) {
                    $hash = md5($lastID . rand().microtime(true));
                    $query = 'update '.$blogTablePrefix.'blogcomments set comment_hash = ' . $GLOBALS['POOL']->db->quote($hashPrefix . $hash) . ' where id = ' . $lastID; 
                    $GLOBALS['POOL']->dbwrite->query($query);
                    $fields['accepturi'] .= " ".BX_WEBROOT.'admin/webinc/approval/?hash='.$hashPrefix.$hash;  
                } else {
                    $fields['accepturi'] .= " Please update your Flux CMS DB to use that feature.";
                }
                $fields['edituri'] = BX_WEBROOT.'admin/edit/blog/sub/comments/?id='.$lastID;
                $emailSubject .= "New comment on '" . html_entity_decode($row['post_title'],ENT_QUOTES,'ISO-8859-1') . "'";
                
                $bodyID = $screenNode->getAttribute('emailBodyID');
                
                if(!empty($bodyID)) {
                    $emailBodyID = $bodyID;
                }
                
                $emailBody = "";
                if ($commentRejected) {
                    $emailBody .= "Comment rejected, due to:\n";
                    $emailBody .= $commentRejected ."\n";
                }
                if ($xblcheck) {
                    $emailBody .= $xblcheck ."\n";
                }
                if(!empty($emailBodyID)) {
                    $emailBody .= utf8_decode($this->parent->lookup($emailBodyID));
                    $this->parent->_replaceTextFields($emailBody, $fields);
                    $emailBody = html_entity_decode($emailBody,ENT_QUOTES,'UTF-8');
                } else {
                    foreach ($fields as $key => $value) {
                        $emailBody .= html_entity_decode("$key: $value",ENT_QUOTES,'UTF-8')."\n";
                    }
                }
                
                $headers = '';
                
                if(!empty($emailFrom)) {
                    $headers .= "From: $emailFrom\r\n";
                }
                //utf 8 encoded...
                //FIXME: do the same for subjects with quoted printable
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
                $emailBody = str_replace('<br />','',$emailBody);
                //don't send mails on rejects for the time beeing
                if ($GLOBALS['POOL']->config->blogSendRejectedCommentNotification == "true" || !$deleteIt) {
                    bx_notificationmanager::sendToDefault($emailTo,$emailSubject, $emailBody,$emailFrom);
                }
            $_SESSION["bx_wizard"] = array();
            
            if(!$commentRejected) {
                bx_plugins_blog_commentsnotification::sendNotificationMails($lastID,$row['id'],$parts['coll']->uri);
                
                header ('Location: '. bx_helpers_uri::getLocationUri($row["post_uri"]) . '.html?sent='.time().'#comment'.$lastID);
            } else {
                //put it in the db;
                $query = 'update '.$blogTablePrefix.'blogcomments set comment_rejectreason = ' . $GLOBALS['POOL']->db->quote(htmlspecialchars($commentRejected)) . ' where id = ' . $lastID; 
                $res = $GLOBALS['POOL']->dbwrite->query($query);
                if ($deleteIt) {
                    print ("Comment rejected. Looks like blogspam.");
                } else {
                    print ("<h1>Possible blogspam</h1>Your comment is considered as possible blogspam and therefore moderated. <br/> If it's legitimate, the author will make it available later.<br/> Your message is not lost ;) <br/>Thanks for your understanding.<p/>");
                    print ("The reasons are: <br/>");
                    print nl2br(htmlspecialchars($commentRejected));
                }
            }
            exit();
            return TRUE;
    }
    
    protected function checkCaptcha($captcha, $imgid) {
        $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
        $ok = false;
        $magickey = $GLOBALS['POOL']->config->magicKey;
        preg_match("#.*.html#", $_SERVER['REQUEST_URI'], $matches);
        
        if($imgid == md5($captcha.floor(time()/(60*15)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0']) or $imgid == md5($captcha.floor(time()/(60*15-1)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0'])) {
                unlink(BX_PROJECT_DIR.'dynimages/'.$imgid . '.png');
                return true;
            } else {
                unlink(BX_PROJECT_DIR.'dynimages/'.$imgid . '.png');
                echo '<script type="text/javascript">alert("False captcha code!!! Please enter the right one from image")</script>';
                return false;
            }
    }
}

?>
