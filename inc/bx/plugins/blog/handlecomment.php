<?php

class bx_plugins_blog_handlecomment {

    
    static private $allowedTags = array('b','i','a','ul','li','ol','pre','blockquote','br','p');
    
    
    static private $tidyOptions = array(
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
    
 function handlePost ($path,$id, $data)  {
            
        //add some more data and clean some others
        $data['remote_ip'] = $_SERVER['REMOTE_ADDR'];

        if(!($data['name'] && $data['comments'])) {
            
            return '<i18n:text i18n:key="blogFieldsMissing">Please fill in all needed fields</i18n:text>';
        }
        
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        $isok = false;
        foreach($data as $name => $value) {
            $data[$name] = bx_helpers_string::utf2entities(trim($value));
        }

        if (($pos = strrpos($id,"/")) > 0) {
            $id = substr($id, $pos + 1);
        };
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        $blogTablePrefix = $tablePrefix.$p->getParameter($parts['coll']->uri,"tableprefix");
        $db = $GLOBALS['POOL']->db;
        
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        blogposts.post_title,
        blogposts.post_uri,
        users.user_login,
        unix_timestamp(blogposts.post_date) as unixtime,
        blogposts.post_comment_mode
        
        from '.$blogTablePrefix.'blogposts as blogposts left join '.$tablePrefix.'users as users on blogposts.post_author = users.user_login
        where blogposts.post_uri = "'.$id.'" ';
        $res = $db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
        
        $isCaptcha = bx_helpers_captcha::isCaptcha($days, (int) $row['unixtime']);
        
        //if captcha is active
        if($isCaptcha == true) {
            if (!bx_helpers_captcha::checkCaptcha($data['passphrase'], $data['imgid'])) {
              return '<i18n:text i18n:key="blogCaptchaWrong">Captcha Number is not correct please try again.</i18n:text>';  
            }
        }
        
        
        if ($row['post_comment_mode'] == 99) {
            $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
        }
        if (!($row['post_comment_mode'] == 2 || $row['post_comment_mode'] == 4 || ($row['post_comment_mode'] == 1 && (time() - 2678800) < $row['unixtime']))) {
            die("No comments allowed anymore...");
        }
        $commentRejected = "";
        if ($row['post_comment_mode'] == 4) {
            $commentRejected = "* By default moderated comment\n";
        }
        
        
        //check remember stuff
        if(!empty($data['remember'])) {
            if($data['name']) {
                setcookie("fluxcms_blogcomments[name]", $data['name'], time()+30*24*60*60, '/');
            }
            if($data['email']) {
                setcookie("fluxcms_blogcomments[email]", $data['email'], time()+30*24*60*60, '/');
            }
            
            if($data['openid_url']) {
                setcookie("fluxcms_blogcomments[openid_url]", $data['openid_url'], time()+30*24*60*60, '/');
            }
        }
        // if name and comment is set and remember box not checked -> delete cookie
        else {
            if (isset($_COOKIE['fluxcms_blogcomments'])) {
                if (isset($_COOKIE['fluxcms_blogcomments']['name'])) {
                    setcookie("fluxcms_blogcomments[name]", '', 0, "/");
                }
                if (isset($_COOKIE['fluxcms_blogcomments']['email'])) {
                    setcookie("fluxcms_blogcomments[email]", '', 0, "/");
                }
                if (isset($_COOKIE['fluxcms_blogcomments']['openid_url'])) {
                    setcookie("fluxcms_blogcomments[openid_url]", '', 0, "/");
                }
            }
             
        }
        
        
        /* flood-protection */
        /*$query = "SELECT unix_timestamp(comment_date)  FROM ".$blogTablePrefix."blogcomments WHERE comment_author_IP='".$_SERVER['REMOTE_ADDR']."' ORDER BY comment_date DESC LIMIT 1";
        
        $res = $GLOBALS['POOL']->db->query($query);
        $time_lastcomment = $res->fetchOne(0);
        if (time()  - $time_lastcomment) < 60){
            die ("Flood protection! You're not allowed to post comments within that short of a timespan");
        } */
        /* end flood-protection */
        
        
        $data['uri'] = BX_WEBROOT_W.$parts['coll']->uri.'archive/'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
        
        // clean up comment
        $data['comments'] = self::cleanUpComment($data['comments']);
        
        //deleteIt == true => Rejected comment
        $deleteIt = false;
        
        if (strpos($_SERVER['REQUEST_URI'],"#") !== false) {
            $commentRejected .= '* # in Uri... ('.$_SERVER['REQUEST_URI'].").\n";
            $deleteIt = true;
        }
        //if uri in post is the same as in the session then do openid = true(1)
        @session_start();
        if(isset($_SESSION['flux_openid_url'] ) && $_SESSION['flux_openid_url'] == $data['openid_url']) {
            $openid = 1;
        } else {
            $openid = 0;
        }
        
        $username = bx_helpers_perm::getUsername();
        
        /* known spammer user */
        $simplecache = popoon_helpers_simplecache::getInstance();
        $simplecache->cacheDir = BX_TEMP_DIR;
        //check for pineapleproxy
        if (isset($_SERVER['HTTP_VIA']) && stripos($_SERVER['HTTP_VIA'],'pinappleproxy') !== false) {
            $commentRejected .= "* Uses known spammer proxy: ". $_SERVER['HTTP_VIA'] . "\n";
        }
                
        
        /* If url field is filled in, it was a bot ...*/
        if (isset($data['url']) && $data['url'] != "") {
            $commentRejected .= "* Hidden URL field was not empty, assuming bot: " . $data['url']."\n";
            
            if(strpos($data['url'], "\n") !== FALSE or strpos($data['url'], "\r") !== FALSE) {
                $commentRejected .= "* Multi line hidden URL field \n";
            }
            self::discardIt(" Hidden URL field was not empty, assuming bot: " . $data['url'],$data['comments']);
            $deleteIt = true;
        }
        
        if (stripos($data['comments'],"[URL") !== false) {
            $commentRejected .= "* Non-supported '[URL]' used\n";
            self::discardIt(" Non-supported '[URL]' used",$data['comments']);
        }
        /* Max 5 links per post and SURBL check */
        /* No check, when logged in */
        if (!$username && (preg_match_all("#http://[\/\w\.\-]+|[\/\w\.\-]+@[\/\w\.\-]+#",$data['comments'], $matches) || $data['openid_url'] != '')) {
            $maxurls = 5;
            if ($data['openid_url'] != '') {
                $matches[0][] = $data['openid_url'] ;
                $maxurls++;
            }
            if (!empty($data['email']) && strpos($data['email'],"@")) {
                $matches[0][] = $data['email'];   
                $maxurls++;
            }
            if (isset($matches[0])) {
                $urls = array_unique($matches[0]);
                if ( count($urls) > $maxurls) {
                    $commentRejected .= "* More than 5 unique links in comment (".count($urls) .")\n";
                    if (count($urls) > ($maxurls + 5)) {
                        $deleteIt = true;
                       if (count($urls) >= $maxurls + 10) {
                           self::discardIt(" More than 15 unique links in comment  (". count($urls) .")", $data['comments']);
                       }
                    }
                }
                $_rbl = bx_plugins_blog_spam::checkRBLs($urls);
                if ($_rbl) {
                    $commentRejected .= $_rbl;
                    $deleteIt = true;
                }
            }
        }
        
        //check sender IP against xbl.spamhaus.org
        
        if (!$username) {
            $xblcheck = bx_plugins_blog_spam::checkSenderIPBLs($_SERVER['REMOTE_ADDR']);
        } else {
            $xblcheck = '';
        }
        $comment_notification_hash = bx_helpers_int::getRandomHex($data['email']); 
        $comment_hash = bx_helpers_int::getRandomHex(md5($commentRejected)); 
        if (!$deleteIt) {  
            
                include_once(BX_LIBS_DIR.'plugins/blog/akismet2.php');
                    
                $akismet = new Akismet2(BX_WEBROOT.$path,'');
                $akismet->setAkismetServer("rest.flux-cms.org");
                $akismet->setCommentAuthor($data['name']);
                $akismet->setCommentAuthorEmail($data['email']);
                $akismet->setCommentAuthorURL($data['openid_url']);
                $akismet->setCommentContent($data['comments']);
                $akismet->setPermalink(BX_WEBROOT.$path.$id);
                $akismet->setParam("comment_hash",md5($comment_hash));
                $isSpam = explode("\n",$akismet->isCommentSpam());
                if (!empty($isSpam[0]) && $isSpam[0] == 'true') {
                  $commentRejected .= "* rest.flux-cms.org thinks, this is spam\n";
                  array_shift($isSpam);
                  $_spamLevel = array_shift($isSpam);
                  $commentRejected .= implode("\n",$isSpam);
                  $deleteIt = true;
                }
        }
        
        //akismet 
        if (!$deleteIt) {  
            
            $akismetkey = $GLOBALS['POOL']->config->blogAkismetKey;
            if ($akismetkey) {
                try {                
                    include_once(BX_LIBS_DIR.'plugins/blog/akismet.php');
                    
                    $akismet = new Akismet(BX_WEBROOT.$path,$akismetkey);
                    $akismet->setCommentAuthor($data['name']);
                    $akismet->setCommentAuthorEmail($data['email']);
                    $akismet->setCommentAuthorURL($data['openid_url']);
                    $akismet->setCommentContent($data['comments']);
                    $akismet->setPermalink(BX_WEBROOT.$path.$id);
                    if($akismet->isCommentSpam()) {
                        $commentRejected .= "* akismet.com thinks, this is spam";
                        $deleteIt = true;
                        if (!empty($urls) && ( count($urls) > 0)) {
                            $simplecache = popoon_helpers_simplecache::getInstance();
                            $simplecache->cacheDir = BX_TEMP_DIR;
                            $_u = "?from=".urlencode(BX_WEBROOT) ."&urls=".urlencode(implode(";",$urls));
                            $simplecache->simpleCacheHttpRead('http://www.bitflux.org/download/antispam/blockedurls.php'.$_u,3600);
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log("Akismet Exception " . $e->getMessage());
                }
            }
        }
        
        
        
        if (!$commentRejected) {
            // insert comment
            $comment_status = 1;
        } else if ($deleteIt) {
            $comment_status = 3;
        } else {
            $comment_status = 2;
        }
        //delete all rejected comments older than 5 days...
        $query = 'delete from '.$blogTablePrefix.'blogcomments where comment_status = 3 and DATE_SUB(now(), INTERVAL 5 DAY) > comment_date ';
        $res = $GLOBALS['POOL']->dbwrite->query($query);

        //delete all moderated comments older than 14 days...
        $query = 'delete from '.$blogTablePrefix.'blogcomments where comment_status = 2 and DATE_SUB(now(), INTERVAL 14 DAY) > comment_date ';
        $res = $GLOBALS['POOL']->dbwrite->query($query);        
        
        $emailFrom = str_replace(":"," ",html_entity_decode($data['name'],ENT_QUOTES,'ISO-8859-1'));
        
        if ($data['email']) {
            $emailFrom .= ' <'.html_entity_decode($data['email'],ENT_QUOTES,'ISO-8859-1').'>';
        } else {
            $emailFrom .= ' <unknown@example.org>';
        }
        // check if emailFrom is a valid input. if not -> reject!!!
        if(strpos($emailFrom, "\n") !== FALSE or strpos($emailFrom, "\r") !== FALSE) {
            print ("Comment rejected. Looks like you're trying to spam the world....");
            die();
        }
        $db = $GLOBALS['POOL']->dbwrite;
        if (!isset($data['comment_notification'])) {
            $data['comment_notification'] = 0;
        } else if (strlen($data['comment_notification'] > 2)) {
            $deleteIt= true;
            $commentRejected .= "* Notification value too long\n";
        } else {
            $data['comment_notification'] = 1;
        }
            
       
        

        $query =     'insert into '.$blogTablePrefix.'blogcomments (comment_posts_id, comment_author, comment_author_email, comment_author_ip,
            comment_date, comment_content,comment_status, comment_notification, comment_notification_hash,
            comment_author_url, openid, comment_username         
            ) VALUES ("'.$row['id'].'",'.$db->quote($data['name'])
            .','.$db->quote($data['email'],'text').','.$db->quote($data['remote_ip']).',
            "'.gmdate('c').'",'.$db->quote(bx_helpers_string::utf2entities($data['comments'])).',
            '.$comment_status.','.$db->quote($data['comment_notification']).',
            "'.$comment_notification_hash.'",'.$db->quote($data['openid_url'],'text').', '.$openid.',
            '.$db->quote($username).')';
        
        if (!trim($data['name'])) {
            $commentRejected .= "* Name was empty: '".$data['name']."'\n";
        }
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        $GLOBALS['POOL']->dbwrite->loadModule('Extended',null,false); 
        $lastID = $GLOBALS['POOL']->dbwrite->getAfterID(null,$blogTablePrefix.'blogcomments');
                
        $data['edituri'] = BX_WEBROOT.'admin/?edit=/forms/blogcomments/?id='.$lastID;
        $data['uri'] .= '#comment'.$lastID;
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
                    $data['accepturi'] = "(Click the link to accept this comment [1]):\n";
                } else {
                    $hashPrefix = "r";
                    $data['accepturi'] = "(Click the link to reject this comment [1]) :\n";
                }
                // insert hash
                $query = 'update '.$blogTablePrefix.'blogcomments set comment_hash = ' . $GLOBALS['POOL']->db->quote($hashPrefix . $comment_hash) . ' where id = ' . $lastID; 
                $GLOBALS['POOL']->dbwrite->query($query);
                $data['accepturi'] .= " ".BX_WEBROOT.'admin/webinc/approval/?hash='.$hashPrefix.$comment_hash;  
                $data['edituri'] = BX_WEBROOT.'admin/edit/blog/sub/comments/?id='.$lastID;
                $emailSubject .= "New comment on '" . html_entity_decode($row['post_title'],ENT_QUOTES,'ISO-8859-1') . "'";
                
                //$bodyID = $screenNode->getAttribute('emailBodyID');
                
                
                
                $emailBody = "";
                if ($commentRejected) {
                    $emailBody .= "Comment rejected, due to:\n";
                    $emailBody .= $commentRejected ."\n";
                }
                if ($xblcheck) {
                    $emailBody .= $xblcheck ."\n";
                }
                $emailBodyID = 'emailBodyAnfrage';
                
                $emailBody .= utf8_decode(self::lookup($emailBodyID));
                //BC with old blogcomments.xml files
                $data['base'] = $data['openid_url'];
                self::_replaceTextFields($emailBody, $data);
                $emailBody = html_entity_decode($emailBody,ENT_QUOTES,'UTF-8');
                
                $headers = '';
                
                if(!empty($emailFrom)) {
                    $headers .= "From: $emailFrom\r\n";
                }
                //utf 8 encoded...
                //FIXME: do the same for subjects with quoted printable
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
                $emailBody = str_replace('<br />','',$emailBody);
                //don't send mails on rejects for the time beeing
                $rejStage = 0;
                if ($commentRejected) {
                    $rejStage = 1;
                    if ($deleteIt) {
                        $rejStage = 2;
                    }
                }
                if ($rejStage == 0 ||
                    ($rejStage == 1 && $GLOBALS['POOL']->config->blogSendModeratedCommentNotification == "true") ||
                    ($rejStage == 2 && $GLOBALS['POOL']->config->blogSendRejectedCommentNotification == "true")
                    ) {
                        bx_notificationmanager::sendToDefault($emailTo,$emailSubject, $emailBody,$emailFrom);
                }
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
                        if ($row['post_comment_mode'] == 4) {
                            header ('Location: '. bx_helpers_uri::getLocationUri($row["post_uri"]) . '.html?mod=1&sent='.time().'#commentNotice');
                        } else {
                            print ("<h1>Possible blogspam</h1>Your comment is considered as possible blogspam and therefore moderated. <br/> If it's legitimate, the author will make it available later.<br/> Your message is not lost ;) <br/>Thanks for your understanding.<p/>");
                            print ("The reasons are: <br/>");
                            print nl2br(htmlspecialchars($commentRejected));
                            print "<hr/>Your comment was:<hr/>" . str_replace("&lt;br /&gt;","<br />",htmlentities($data['comments']) )."<hr/>";
                        }
                    }
                }
                exit();
                return FALSE;
 }

    
    /* from formwizard php */
    static public function lookup($name) {
        
        $config = new DomDocument();
            $config->load(BX_PROJECT_DIR."/xml/blogcomment.xml");
            
            $confctxt= new DOMxpath($config);
            $confctxt->registerNameSpace("bxco","http://bitflux.org/config/1.0");
            
        
        // this could be done with one xpath-query
        // try reequested language
        $entryNS = $confctxt->query("//bxco:entry[@ID='$name']/bxco:text[1]");
        $entryNode = $entryNS->item(0);
        if(!empty($entryNode)) {
            $childNode = $entryNode->firstChild;
            if(!empty($childNode)) {
                $text = $childNode->nodeValue; 
                if(!empty($text)) {
                    return $text;
                }
            }
        }
        
        
        return $name;
    }
    
    static function _replaceTextFields(&$subject, $textfields) {
        foreach($textfields as $field => $value) {
            $patterns[] = '/\{'.$field.'\}/';
            $replacements[] = $value;
        }
        $subject = preg_replace($patterns, $replacements, $subject);
    }
    
    static protected function discardIt($msg,$comment) {
         
            print ("Comment rejected. Looks like blogspam.<br/>");
            print "Your comment was:<hr/>" . str_replace("&lt;br /&gt;","<br />",htmlentities($comment))."<hr/>";
	       error_log("Flux: Blog Comment Discarded for " . BX_WEBROOT ." : ". $msg);
            die();
    }
    
    static public function cleanUpComment($body) {
            
        if (class_exists('tidy')) {
            $tidy = new tidy();
            if(!$tidy) {
                throw new Exception("Something went wrong with tidy initialisation. Maybe you didn't enable ext/tidy in your PHP installation. Either install it or remove the tidy transformer from your sitemap.xml");
            }
        } else {
            $tidy = false;
        }
        
        // this preg escapes all not allowed tags...
        
        $_tags = implode("|",self::$allowedTags).")])#i";
        $body = preg_replace("#\<(/[^(".$_tags,"&lt;$1", $body);
        $body = preg_replace("#\<([^(/|".$_tags,"&lt;$1", $body);
        $allowedTagsString = "<".implode("><",self::$allowedTags).">";
        if ($tidy) {
            $tidy->parseString(strip_tags(nl2br($body),$allowedTagsString ),self::$tidyOptions,"utf8");
            $tidy->cleanRepair();
            $body = popoon_classes_externalinput::basicClean((string) $tidy);
            // and tidy it again 
            $tidy->parseString($body);
            $tidy->cleanRepair();
            $body = (string) $tidy;
        } else {
            $body =  popoon_classes_externalinput::basicClean(strip_tags(nl2br($body),$allowedTagsString));
            $dom = new domdocument();
            $dom->recover = true;
            // check if wellformed
            if ($dom->loadXML('<body>'.$body.'</body>')) {
                $body = "";
                foreach($dom->documentElement->childNodes as $node) {
                    $body .= $dom->saveXML($node);
                }
            } else {
                //just strip all tags, if we can't recover and it's not wellformed
                $body = nl2br(strip_tags($body));
            }
                
        }
        return $body;    
    }
    
    
}
