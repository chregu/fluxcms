<?php

/*

table you need to use generalcomments plugin

CREATE TABLE `fluxcms2_comments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `comment_posts_id` int(11) NOT NULL default '0',
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL default '',
  `comment_author_url` varchar(100) NOT NULL default '',
  `comment_author_ip` varchar(100) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment_type` varchar(20) NOT NULL default '',
  `comment_status` tinyint(4) NOT NULL default '1',
  `comment_rejectreason` text,
  `comment_hash` varchar(33) default NULL,
  `comment_notification` tinyint(4) default '0',
  `comment_notification_hash` varchar(32) default '',
  `openid` tinyint(4) NOT NULL default '0',
  `comment_username` varchar(100) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `comment_posts_id` (`comment_posts_id`),
  KEY `comment_status` (`comment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

*/

class bx_plugins_comments extends bx_plugin implements bxIplugin {

	static private $allowedTags = array('b','i','a','ul','li','ol','pre','blockquote','br','p');
    static protected $timezone = null;
    static protected $timezoneString = null;
    
    
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
	
    static public $instance = array();
    protected $res = array();

    public $commentTable = "comments";

    protected $db = null;
    protected $tablePrefix = null;

    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_comments($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
    }

    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        
        return $name.'.'.$this->name;
       
    }

    public function getContentById($path, $id){
		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		if (self::$timezone === NULL) {
               self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
        if (!self::$timezoneString) {
             self::$timezoneString = bx_helpers_config::getTimezoneAsString();
        }
		
		if(isset($_POST) && $_POST) {
			$this->handleCommentPost($path, $id, $_POST);
		}
		
		$dom = new domDocument();
		$xml = '<div class="comments_new">';
        
		$query = "select id, openid, comment_author, DATE_FORMAT(date_add(comment_date, INTERVAL ". self::$timezone." SECOND),'%d.%m.%Y %H:%i') as comment_date, comment_author_email, comment_type, comment_author_url, comment_content from ".$prefix."comments where comment_status = 1 and path = '".BX_WEBROOT.$_SERVER['REQUEST_URI']."' order by ".$prefix."comments.comment_date";
		$cres = $GLOBALS['POOL']->db->query($query);
		if (MDB2::isError($cres)) {
			throw new PopoonDBException($cres);
		}
		$xml .= $this->getComments($cres);
        
		$xml .= $this->getCommentForm($emailBodyID = '', $path, $imgid='', $isCaptcha='0');
		
		$xml .= "</div>";
		$dom->loadXML($xml) or die("müll");
		
		return $dom;
    }
	
	protected function handleCommentPost($path, $id, $data) {
		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		if(!($data['name'] && $data['comments'])) {
            
            return '<i18n:text i18n:key="blogFieldsMissing">Please fill in all needed fields</i18n:text>';
        }
            
        //add some more data and clean some others
        $data['remote_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['email'] = strip_tags($data['email'] );
        $data['name'] = strip_tags($data['name']);
        
     
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        $isok = false;
        foreach($data as $name => $value) {
            if(!is_array($data[$name])) {
				$data[$name] = bx_helpers_string::utf2entities(str_replace("&","&amp;",trim($value)));
			}
        }

        if (($pos = strrpos($id,"/")) > 0) {
            $id = substr($id, $pos + 1);
        };
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $db = $GLOBALS['POOL']->db;
        
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        blogposts.post_title,
        blogposts.post_uri,
        users.user_login,
        unix_timestamp(blogposts.post_date) as unixtime,
        blogposts.post_comment_mode
        
        from '.$prefix.'blogposts as blogposts left join '.$prefix.'users as users on blogposts.post_author = users.user_login
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
        
        
        $commentRejected = "";
        
        if (strpos($_SERVER['REQUEST_URI'],"#") !== false) {
		$commentRejected .= '* # in Uri... ('.$_SERVER['REQUEST_URI'].").\n";
	}
        //if uri in post is the same as in the session then do openid = true(1)
        @session_start();
        	
        $username = bx_helpers_perm::getUsername();
        
        /* known spammer user */
        $simplecache = popoon_helpers_simplecache::getInstance();
        $simplecache->cacheDir = BX_TEMP_DIR;
        //deleteIt == true => Rejected comment
        $deleteIt = false;
        //check for pineapleproxy
        if (isset($_SERVER['HTTP_VIA']) && stripos($_SERVER['HTTP_VIA'],'pinappleproxy') !== false) {
            $commentRejected .= "* Uses known spammer proxy: ". $_SERVER['HTTP_VIA'] . "\n";
        }
        
        /* If url field is filled in, it was a bot ...*/
        if (isset($data['url']) && $data['url'] != "") {
            $commentRejected .= "* Hidden URL field was not empty, assuming bot: " . $data['url']."\n";
            
            if(strpos($data['url'], "\n") !== FALSE or strpos($data['url'], "\r") !== FALSE) {
                $commentReject .= "* Multi line hidden URL field \n";
            }
            self::discardIt(" Hidden URL field was not empty, assuming bot: " . $data['url']);
            $deleteIt = true;
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
        //akismet 
        if (!$deleteIt) {  
            
            $akismetkey = $GLOBALS['POOL']->config->blogAkismetKey;
            if ($akismetkey) {
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
        $query = 'delete from '.$prefix.'comments where comment_status = 3 and DATE_SUB(now(), INTERVAL 5 DAY) > comment_date ';
        $res = $GLOBALS['POOL']->dbwrite->query($query);

        //delete all moderated comments older than 14 days...
        $query = 'delete from '.$prefix.'comments where comment_status = 2 and DATE_SUB(now(), INTERVAL 14 DAY) > comment_date ';
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
        $comment_notification_hash = md5($data['email'] . rand().microtime(true));
        $db = $GLOBALS['POOL']->dbwrite;
        if (!isset($data['comment_notification'])) {
            $data['comment_notification'] = 0;
        } else if (strlen($data['comment_notification'] > 2)) {
            $deleteIt= true;
            $commentRejected .= "* Notification value too long\n";
        }
		$commentpath = BX_WEBROOT.$_SERVER['REQUEST_URI'];
		$query =     'insert into '.$prefix.'comments (comment_posts_id, comment_author, comment_author_email, comment_author_ip,
            comment_date, comment_content,comment_status, comment_notification, comment_notification_hash,
            comment_author_url, comment_username, path
            ) VALUES ("'.$row['id'].'",'.$db->quote($data['name'])
            .','.$db->quote($data['email'],'text').','.$db->quote($data['remote_ip']).',
            "'.gmdate('c').'",'.$db->quote(bx_helpers_string::utf2entities($data['comments'])).',
            '.$comment_status.','.$db->quote($data['comment_notification']).',
            "'.$comment_notification_hash.'",'.$db->quote($data['openid_url'],'text').',
            '.$db->quote($username).', "'.$commentpath.'")';
			
			if (!trim($data['name'])) {
				$commentRejected .= "* Name was empty: '".$data['name']."'\n";
		}
		bx_helpers_debug::webdump($query);
		$res = $GLOBALS['POOL']->dbwrite->query($query);
        
		
	}
		
	protected function getCommentForm($emailBodyID = '', $posturipath, $imgid = null, $isCaptcha) {        
        
        $remember = null;
        //$data = $this->commentData;
        $data = null;
		if($data == null) {
            $data['name'] = null;
            $data['email'] = null;
            $data['comments'] = null;
        }
        
		
        if (isset($_COOKIE['fluxcms_blogcomments'])) {
			foreach ($_COOKIE['fluxcms_blogcomments'] as $name => $value) {
				if (!isset($data[$name]) || !$data[$name]) {
					$data[$name] = $value;
				}
			}
			$remember = 'checked';
		} else if ($_uname = bx_helpers_perm::getUsername()) {
			$data['name'] = $_uname;
			if (!empty($_SESSION['_authsession']['data']['user_email'])) {
				$data['email'] = $_SESSION['_authsession']['data']['user_email'];
			}
			$data['openid_url'] = BX_WEBROOT;
		}   else {
			$remember = null;                                                        
		}
        //get TablePrefix
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
          
         
         
        //if ($this->newCommentError) {
        //   $xml .= '<p style="color:red;">'.$this->newCommentError.'</p>';
        //}
            $xml = '<form name="bx_foo" action="'.$_SERVER['REQUEST_URI'].'#commentform" method="post">
               <table class="form" style="margin-left:25px;" border="0" cellspacing="0" cellpadding="0" id="commentform">
               <tr>
               <td valign="top"><i18n:text i18n:key="blogCommentName">Name</i18n:text>*</td>
               <td class="formHeader" valign="middle"><input class="formgenerell" type="text" name="name" id="name" value="'.$data['name'].'"/></td>
               </tr>
			   
			   <tr>
               <td valign="top"><i18n:text i18n:key="blogCommentEmail">E-Mail</i18n:text></td>
               <td><input class="formgenerell" type="text" name="email" id="email" value="'.$data['email'].'"/></td>
               </tr>
                <tr><td valign="top" width="90" class="formurl">For Spammers Only</td><td valign="middle" class="formurl"><input type="text" name="url" value="" class="formurl" /></td></tr>
             
               <tr>
               <td valign="top"><i18n:text i18n:key="blogCommentURL">URL</i18n:text></td>
			   <td>
                    <input class="formgenerell" type="text" id="openid_url" name="openid_url" value="'.$data['openid_url'].'"/></td>
               ';
                    
               $xml .= '
               </tr>';
               
               if(isset($_COOKIE['openid_enabled']) && $_COOKIE['openid_enabled']) {
                    if(isset($_SESSION['flux_openid_url']) && $_SESSION['flux_openid_url']) {
                        //continue();
                    } else {
                        if(isset($_SESSION['flux_openid_immediate_checked']) && $_SESSION['flux_openid_immediate_checked']) {
                            $immediate = false;
                        } else {
                            $immediate = true;
                        }
                        $_SESSION['flux_openid_immediate_checked'] = true;
                    }
                }
               if(isset($immediate) && $immediate == true) {
                   $xml .= '<iframe id="foo"  style="display: none;"/>';
                   
                   $process_url = BX_WEBROOT.'inc/bx/php/openid/finish_auth.php';
                   $trust_root = BX_WEBROOT;
                   $store_path = BX_TEMP_DIR."_php_consumer_test";
                   
                   require_once "Auth/OpenID/Consumer.php";
                   
                   require_once "Auth/OpenID/FileStore.php";
                   $store = new Auth_OpenID_FileStore($store_path);
                   
                   $consumer = new Auth_OpenID_Consumer($store, null,true);

                   // Begin the OpenID authentication process.
                   list($status, $info) = $consumer->beginAuth($_COOKIE['openid_enabled']);
                   // Handle failure status return values.
                   if ($status != Auth_OpenID_SUCCESS) {
                       $error = "Authentication error.";
                       //include 'index.php';
                   }
                   // Redirect the user to the OpenID server for authentication.  Store
                   // the token for this authentication so we can verify the response.
                   $_SESSION['openid_token'] = $info->token;
                   $redirect_url = $consumer->constructRedirect($info, $process_url, $trust_root);
                   
                   $xml .= '<tr><td></td><td><iframe src="'.$redirect_url.'" style="display: block; height:35px;" /></td></tr>';
               }
               $xml .= '<tr>
               <td valign="top"><i18n:text i18n:key="blogCommentComment">Comment</i18n:text>*</td>
               <td><textarea rows="10" cols="40" name="comments">'.$data['comments'].'</textarea></td>
               </tr><tr>
               <td colspan="2" valign="top"><input type="checkbox" name="comment_notification" />
               <i18n:text i18n:key="blogCommentNotify">Notify me via E-Mail when new comments are made to this entry</i18n:text></td>
                </tr>';
                if($remember == "checked" || (!empty($_COOKIE['openid_enabled']))) {
                    $xml .= '<tr><td colspan="2" valign="top"><input type="checkbox" name="remember" checked="checked"/>';
                } else {
                       $xml .= '<tr><td colspan="2" valign="top"><input type="checkbox" name="remember"/>';
                 }
                  $xml .= ' <i18n:text i18n:key="blogCommentRemember">Remember me (needs cookies)</i18n:text></td></tr>';
                  
				  // captcha stuff
				  
				  $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
				  $isCaptcha = bx_helpers_captcha::isCaptcha($days);
				  
				  if($isCaptcha == 1) {
						// generate captcha
						$imgid = bx_helpers_captcha::doCaptcha();
				  }
                
				if($isCaptcha == 1 and isset($imgid)) {
                    $xml .= '<tr>
                    <td colspan="2"><br/><i18n:text i18n:key="blogCommentCaptcha">Anti-Spam check, please copy the letters to the input field</i18n:text></td>
                    </tr>
                    <tr>
                    <td>
                        <img src="'.BX_WEBROOT.'dynimages/captchas/'.$imgid.'.png" alt="captcha"/>
                    </td><td>
                        &#160;
                        <input name="passphrase" type="text" class="captcha"/>
                        <input name="imgid" type="hidden" value="'.$imgid.'"/>
                        </td>
                    </tr>
                    ';
                }
                
                $xml .= '<tr>
                <td></td>
                <td><br /><input type="submit" i18n:attr="value" id="bx[plugins][blog][_all]" name="bx[plugins][blog][_all]" value="Send" class="formbutton" />
                <!--input onclick="javascript:previewSubmit(this.parentNode);" type="button" i18n:attr="value"  value="Preview" class="formbutton" /-->
                
                </td>
                </tr>
               </table>
               </form>
               
               ';
        
        return $xml;
    }
	
	protected function getComments($res) {
        if (!self::$timezoneString) {
                 self::$timezoneString = bx_helpers_config::getTimezoneAsString();
        }
        if(!MDB2::isError($res)) {
            $xml = '<a name="comments"/><div class="comments">';
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                //$this->lastModified = max ($this->lastModified,$row['lastmodified']);
                $xml .= '<div class="comment" ';
                $xml .= ' id = "'.$row['id'].'"';
                $xml .= '>';
                $xml .= '<a name="comment'.$row['id'].'"/>';
                $xml .= '<div class="comment_meta_data">';
                
                $xml .= '<span class="comment_author_email">'.$row['comment_author_email'].'</span>';
                $xml .= '<span class="comment_author">';
                if ($row['comment_author_url']) {
                    $xml .= '<a href="';
			if (strpos($row['comment_author_url'],'http:') !== 0) {
				$xml .= 'http://';
			}
			$xml .= $row['comment_author_url'].'">'.$row['comment_author'].'</a></span>';
                } else {
                    $xml .= $row['comment_author'].'</span>';
                }
                $xml .= '<span class="comment_date">'.$row['comment_date'].' '. self::$timezoneString . '</span>';
                if($row['openid'] == 1) {
                    $xml .= '<img class="openid" src="'.BX_WEBROOT.'webinc/images/openid.gif"/>';
                }
                
                $xml .= '<span class="comment_type">'.$row['comment_type'].'</span>';
                $xml .= '</div>';
                $xml .= '<div class="comment_content">';

                $xml .= bx_helpers_string::makeLinksClickable($row['comment_content']).'</div>';
                $xml .= "</div>";
				
                
            }
            $xml .= '</div>';

        }
		
        return $xml;

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
        }
    return $body;    
    }
  
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
	
	static protected function discardIt($msg) {
         
            print ("Comment rejected. Looks like blogspam.");
	error_log("Flux: Blog Comment Discarded for " . BX_WEBROOT ." : ". $msg);
            die();
    }
}
?>
