<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                                |     
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
/**
 * class bx_plugins_blog_authorarchive
 * @package bx_plugins
 * @subpackage blog
 */
class bx_plugins_blog extends bx_plugin implements bxIplugin {

    protected $res = array();
    static protected $timezone = null;
    static protected $timezoneString = null;
    public $lastModified = null;

    static public $instance = array();
    static private $idMapper = null;
    static protected $tree = null;
    protected $newCommentError = false;
    protected $commentData = null;
    

    /*** magic methods and functions ***/

    public static function getInstance($mode) {
        if (!isset(bx_plugins_blog::$instance[$mode])) {
            bx_plugins_blog::$instance[$mode] = new bx_plugins_blog($mode);
        }
        return bx_plugins_blog::$instance[$mode];
    }

    public function __construct($mode) {
/*        if (!defined('BX_WEBROOT_W')) {
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            if ($lang != BX_DEFAULT_LANGUAGE) {
                define ('BX_WEBROOT_W', BX_WEBROOT.$lang);
            } else {
                define ('BX_WEBROOT_W',substr( BX_WEBROOT,0,-1));
            }

        }
*/        $this->mode = $mode;
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    }
    
    public function getPermissionList() {
        return array(    "blog-back-post",
                        "blog-back-options",
                        "blog-back-files",
                        "blog-back-gallery",
                        "blog-back-blogroll",
                        "blog-back-categories",
                        "blog-back-private",
                        "admin_dbforms2-back-blogcomments");    
    }

    /**
    * gets the unique id of a resource associated to a request triple
    *
    * @param string $path the collection uri path
    * @param string $name the filename part
    * @param string $ext the extension
    * @return string id
    */
    public function getIdByRequest($path, $name = NULL, $ext  = NULL) {
        $perm = bx_permm::getInstance();
        if (!$perm->isAllowed($path.$name.'.'.$ext,array('read'))) {
            throw new BxPageNotAllowedException();
        }
        return $name;
    }

    public function getLastModifiedById($path, $id) {
        return $this->lastModified;
    }

    public function getEditorsById($path, $id) {

        return array("blog");
    }

    public function getContentUriById ($path, $id) {
        bx_global::registerStream("blog");

        if ($id == "") {
            $id = "entries.xml";
        }
        return("blog://$path$id");
    }

    public function getContentById($path, $id) {
        $perm = bx_permm::getInstance();
        if($id == "newpost" or $id == "_all/index") {
            if (!$perm->isAllowed($path,array('blog-back-post'))) {
                throw new BxPageNotAllowedException();
            }
        }
        $this->setJavaScriptSource('webinc/js/livesearch.js');
        $this->setJavaScriptSource('webinc/js/openId.js');
        
        $blogid = $this->getParameter($path,"blogid");
        if (!$blogid) {$blogid = 1;}
        $maxposts_param = $this->getParameter($path,'maxposts');
        if ($maxposts_param ) { 
            $maxPosts = $maxposts_param;
        } else {
            $maxPosts = 10;
        }
        
        $tablePrefix = $this->tablePrefix.$this->getParameter($path,"tableprefix");
        $perm = bx_permm::getInstance();
        if ($perm->isAllowed($path.$id,array('ishashed','isuser'))) {
            $this->singlePostPerm = 7;
            if ($id == "_all/index") {
                $this->overviewPerm = 7;
            } else {
                $this->overviewPerm = 3;
            }
        } else {
            $this->singlePostPerm = 1;
            $this->overviewPerm = 1;
        }
        $this->checkExpiry = $GLOBALS['POOL']->config->getConfProperty('blogPostsCheckExpiry');
         
        if (strpos($id,"plugin=") === 0) {
            $plugin = substr($id,7);
            if ($pos = strpos($plugin,"(")) {
                $pos2 = strpos($plugin,")");
                $params = substr($plugin,$pos+1, $pos2 - $pos - 1);
                $plugin = substr($plugin,0,$pos);
                $params = explode(",",$params);
            }  else {
                $params = array();
            }
            $plugin = "bx_plugins_blog_".$plugin;
            $xml =  call_user_func(array($plugin,"getContentById"), $path, $id, $params,$this,$tablePrefix);
            if (is_string($xml)) {
                $dom = new DomDocument();
                if (function_exists('iconv')) {
                    $xml =  @iconv("UTF-8","UTF-8//IGNORE",$xml);
                }
                $dom->loadXML($xml);
                return $dom;
            } else {
                return $xml;
            }
        }

        $mode = $this->getParameter($path,"mode");
        if ($mode == "latestcomments") {
            $plugin = "bx_plugins_blog_comments";
            $params = array("latest");
            $xml =  call_user_func(array($plugin,"getContentById"), $path, $id, $params,$this);

            if (is_string($xml)) {
                $dom = new DomDocument();
                $dom->recover = true;
                $dom->loadXML($xml);
                return $dom;
            } else {
                return $xml;
            }
        }
        $startEntry  = isset($_GET['start']) ? $_GET['start'] : 0;
        if (($pos = strrpos($id,"/")) > 0) {
            $cat = substr($id,0,$pos);
            $id = substr($id, $pos + 1);

        } else {
            $cat = "";
        }
    $sidebar = true;
        if ($mode == "rss") {
        $sidebar = false;
            $id = "index";
        }
        
        $query = "SELECT ".$tablePrefix."blogposts.id ";
        
        if (!empty($_GET['qmode'])) {
            if (preg_match('#.*[a-z0-9]$#',$_GET['qmode'])) {
                $plugin = "bx_plugins_blog_qmode_".$_GET['qmode'];
                $query .=  call_user_func(array($plugin,"getQueryFields"), $path, $id, $this, $tablePrefix);
            }
            
        }
        
        $query .= "from ".$tablePrefix."blogposts ";
        
        $archivepath = "";
        $archivewhere = "";
        $total = 0;
        $gmnow = gmdate("Y-m-d H:i:00",time()  + 60);
        $bloglanguage = $GLOBALS['POOL']->config->blogShowOnlyOneLanguage;
        $lang = $GLOBALS['POOL']->config->getOutputLanguage();
        if (!empty($_GET['qmode'])) {
            
            if (preg_match('#.*[a-z0-9]$#',$_GET['qmode'])) {
                $plugin = "bx_plugins_blog_qmode_".$_GET['qmode'];
                $query .=  call_user_func(array($plugin,"getQueryWhere"), $path, $id, $this, $tablePrefix);
            }
            
        } else if (isset($_GET['q']) && !(strpos($_SERVER['REQUEST_URI'], '/search/') === 0)) {
            $cat = "";

            $_GET['q'] =  popoon_classes_externalinput::basicClean(bx_helpers_globals::stripMagicQuotes($_GET['q']));

            $query .=" where (MATCH (post_content,post_title) AGAINST (" . $GLOBALS['POOL']->db->quote($_GET['q']) .") or  post_title like " .  $GLOBALS['POOL']->db->quote("%".$_GET['q']."%")  . ") and ".
            $tablePrefix."blogposts.post_status & ".$this->overviewPerm;
            $query .= " and blog_id = ".$blogid;
            $query .= " and post_date < '".$gmnow."' ";
            $doComments = false; 
            $total = $GLOBALS['POOL']->db->query($query)->fetchOne(0);

            $query .= " order by post_date desc limit ".$startEntry . "," . $maxPosts;
        } else if ($id == "index" ) {
            // category...
             if (strpos($cat,"archive") === 0) {                           
                  $archivepath = $cat;
                 $cat = substr($cat,8);
               if (preg_match("#^([0-9]{4})#",$cat,$matches)) {

                   $archivewhere = " and YEAR(post_date) = " . $matches[1];
                   $cat = substr($cat,5);

                   if (preg_match("#^([0-9]{2})#",$cat,$matches)) {

                       $archivewhere .= " and MONTH(post_date) = ". $matches[1];
                       $cat = substr($cat,3);

                       if (preg_match("#^([0-9]{2})#",$cat,$matches)) {
                           $archivewhere .= " and DAYOFMONTH(post_date) = ". $matches[1];
                           $cat = substr($cat,3);
                       }
                   }
                   $archivewhere .= " and ".$tablePrefix."blogposts.blog_id = ". $blogid;
                   
                   //remove real cat, if exists
                  $archivepath = preg_replace('#'.$cat.'$#','',$archivepath);
               }
               else if (strpos($cat,"author") === 0) {
                   $author = substr($cat,7);
                   $archivewhere .= " and post_author= ". $GLOBALS['POOL']->db->quote($author);
                   $cat = false;   
               } else if (strpos($cat,"id") === 0) {
                   $id = (int) substr($cat,3);
                   
                   $newuri = $this->getNewPermaLink($id,$path,true);
                   if ($newuri) {
                        header("Location: ". BX_WEBROOT_W.$path.$newuri,true,301 );
                        die();
                    }
               } else if (strpos($cat,"tag") === 0) {
                   $tag = substr($cat,4);
                   $tquery="select path from ".$tablePrefix."tags as tags left join ".$tablePrefix."properties2tags as properties2tags
             on tags.id = properties2tags.tag_id where tags.tag = '".$tag."'";
                   $tres = $GLOBALS['POOL']->db->query($tquery);
                   $uris = array();
                   while ($trow = $tres->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                       $uri = preg_replace("#^".$path."#","",$trow['path']);
                       $uris[] = $GLOBALS['POOL']->db->quote(substr($uri,0,-5));
                   }
           if (count ($uris) > 0) {
                       $archivewhere .= " and post_uri in (".implode(",",$uris).")";
           } else {
              $archivewhere .= " and 1 = 2";
           }
                   $cat = false;
               }
               
               else {
                   //FIXME that's somehow ugly, but prevents same content on different urls...
                    header("Location: ".BX_WEBROOT_W.$path.$cat);
                    die();
               }
            }
            else if ($cat == "root") {
                throw new BxPageNotFoundException(substr($_SERVER['REQUEST_URI'],1));
            }
            if (isset($cat)  && $cat && $cat != '_all') {
                $lres = $GLOBALS['POOL']->db->query("select l,r from ".$tablePrefix."blogcategories where ".$tablePrefix."blogcategories.fulluri = '$cat' and ".$tablePrefix."blogcategories.status=1 ");
                if (MDB2::isError($lres)) {
                    throw new PopoonDBException($lres);
                }
                $lrow = $lres->fetchRow(MDB2_FETCHMODE_ASSOC);
            }
            $doComments=false;
            if (isset($lrow)) {
                $leftjoin= " left join ".$tablePrefix."blogposts2categories on ".$tablePrefix."blogposts.id = ".$tablePrefix."blogposts2categories.blogposts_id left join ".$tablePrefix."blogcategories on ".$tablePrefix."blogposts2categories.blogcategories_id = ".$tablePrefix."blogcategories.id where ".$tablePrefix."blogcategories.status = 1  and ".$tablePrefix."blogcategories.l >=
                ".$lrow['l'] . " and  ".$tablePrefix."blogcategories.r <= ".$lrow['r'];
            } else if (!$cat ) {
                $leftjoin= " left join ".$tablePrefix."blogposts2categories on ".$tablePrefix."blogposts.id = ".$tablePrefix."blogposts2categories.blogposts_id left join ".$tablePrefix."blogcategories on ".$tablePrefix."blogposts2categories.blogcategories_id = ".$tablePrefix."blogcategories.id where ".$tablePrefix."blogcategories.l >= 1 and ".$tablePrefix."blogcategories.status=1
                ";
            } else if ($cat == '_all') {
                $leftjoin = "";
                // needed for xmlrpc getting of extended post
                // =2 means, only get extended post and not also the comments...
                $doComments = 2;
            } else {
                throw new BxPageNotFoundException(substr($_SERVER['REQUEST_URI'],1));
            }
            if ($archivewhere || $leftjoin) {
              $archivewhere .= ' and ';  
            } else {
                $archivewhere = ' where ';
            }
            $archivewhere .= $tablePrefix.'blogposts.id > 0 and ' . $tablePrefix.'blogposts.post_status & ' . $this->overviewPerm ;
            $archivewhere .= ' and '.$tablePrefix.'blogposts.blog_id = '.$blogid;
                
            if ($this->overviewPerm != 7) {
                if ($bloglanguage == 'true') {
                    $archivewhere .= ' and ('.$tablePrefix.'blogposts.post_lang = "'.$lang.'" or '.$tablePrefix.'blogposts.post_lang = "")';
                }
                
                $archivewhere .= " and post_date < '".$gmnow."'";
                
                $catAllOnly = $GLOBALS['POOL']->config->getConfProperty('blogPostsExpireCatAllOnly');
                if ($cat == "" || $catAllOnly == "false") {
                    $archivewhere .= " AND (";
                    $archivewhere .= $tablePrefix."blogposts.post_expires = '0000-00-00 00:00:00' OR ";
                    $archivewhere .= $tablePrefix."blogposts.post_expires >= '".$gmnow ."')";
                }
            }
            $res = $GLOBALS['POOL']->db->query("select count(*) as c from ".$tablePrefix."blogposts $leftjoin  $archivewhere group by ".$tablePrefix."blogposts.id ");
            
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }

            $total = $res->numRows();
            $query .= $leftjoin . $archivewhere ;
            $query .= ' group by '.$tablePrefix.'blogposts.id ';

            $query .= 'order by post_date DESC limit '.$startEntry . ','.$maxPosts;
            
        } else {

            if (strpos($id,"_id") === 0 ) {
                $query .= " where ".$tablePrefix."blogposts.id = ".substr($id,3);
                $query .= ' and '.$tablePrefix.'blogposts.post_status & ' . $this->singlePostPerm ; 
            } else {
                if (strpos ($cat , 'archive') === 0 && strlen($cat) < 18)  {
                    $newuri = $this->getNewPermaLink($id,$path);
                    if ($newuri) {
                        header("Location: ". BX_WEBROOT_W.$path.$newuri,true,301 );
                        die();
                    }
                }
                $query .= " where post_uri = '$id'  ";
                
                $query .= ' and '.$tablePrefix.'blogposts.post_status & ' . $this->singlePostPerm ;
            }
            $doComments = true;
        }
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        $xml = '<html xmlns:blog="http://bitflux.org/doctypes/blog" xmlns:i18n="http://apache.org/cocoon/i18n/2.1" xmlns="http://www.w3.org/1999/xhtml"><head><title>';
        if ($cat) {
            $_r = $GLOBALS['POOL']->db->prepare("select fullname from ".$tablePrefix."blogcategories where fulluri = ?",array('text'),array('text'));
            $_r = $_r->execute( array($cat));
            
            if (MDB2::isError($_r)) {
                throw new PopoonDBException($_r);
            }
            $catname = $_r->fetchOne(0);
            $xml .= " :: " . htmlspecialchars(html_entity_decode($catname,ENT_NOQUOTES,'UTF-8'));
        }
        $xml .= '</title></head>';
        $xml .= '<body>';
        
        if ($res->numRows() > 0 ) {
            $xml .= $this->getBlogPosts($res, $path, $doComments );
            
            if (!$doComments || ($doComments == 2 && $total >0)) {
                $end =   (($startEntry + $maxPosts) > $total) ? $total : $startEntry + $maxPosts;
                $xml .= '<div class="blog_pager" blog:start="'.($startEntry + 1).'" blog:end="'.$end.'" blog:total="'.$total.'">';

                $xml .= '<span class="blog_pager_prevnext">';

                $path = BX_WEBROOT_W  .$path.$archivepath.$cat;
                if (substr($path,-1) != "/") {$path .= "/";}
                if (isset($_GET['q'])) {
                    $searchAdd = "&amp;q=".$_GET['q'];
                } else {
                    $searchAdd = "";
                }
                if ($startEntry >= $maxPosts) {
                                    $xml .='<a class="blog_pager_prev" href="./?start='.($startEntry -$maxPosts).$searchAdd.'"><i18n:text>Prev</i18n:text></a>';
                }
                if ($startEntry < ($total - $maxPosts)) {
                    $xml .=' <a class="blog_pager_next" href="./?start='.($startEntry + $maxPosts).$searchAdd.'"><i18n:text>Next</i18n:text></a>';
                }
                $xml .= '</span>';

                $xml .= '<span  class="blog_pager_counter">'.($startEntry + 1) .'-'. ($end) .'/'.$total.'</span>';

                $xml .= '</div>';
            }
        } else if ($id == "index") {
            $xml .= '<div class="entry"><h2 class="post_title">No Entries found</h2></div> ';
        } else {
            throw new BxPageNotFoundException(substr($_SERVER['REQUEST_URI'],1));
        }
        
        $xml .= '</body></html>';
        $dom = new DomDocument();
        $dom->recover = true;
        
        if (!@$dom->loadXML($xml)) {
            //if it didn't work loading, try with replacing ampersand
            //FIXME: DIRTY HACK, works only in special cases..
            $xml = str_replace("&amp;","§amp;",$xml);
            $xml = preg_replace("#\&([^\#])#", "&#38;$1", $xml);
            $xml = str_replace("§amp;","&amp;",$xml);
            $dom->loadXML($xml);
        }
       if ($sidebar && $dom->documentElement) {
            $this->getSidebarData($dom->documentElement);    
    }
        return $dom;
    }
    

    public function getNewPermaLink($uri, $path, $isId = false) {
        $tablePrefix = $this->tablePrefix.$this->getParameter($path,"tableprefix");
        $db = $GLOBALS['POOL']->db;
        $query = "SELECT unix_timestamp(blogposts.post_date) as unixtime,
                    blogposts.post_uri as post_uri
                    FROM ".$tablePrefix."blogposts as blogposts where ";
       if ($isId) {
           $query .= "id = ". $db->quote($uri);
       }   else {
            $query .= "post_uri = ". $db->quote($uri);
       }
       
       $res = $db->query($query);

       if ( !$res || MDB2::isError($res) ) {
           return false;
       } else {
           $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
           return 'archive/'. date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
       }

   }
    protected function getBlogPostData($id,$path,$doComments = false) {
        
        
        $blogid = $this->getParameter($path,"blogid");
        $blograting = $this->getParameter($path,"blograting");
        
        if (!$blogid) {$blogid = 1;};
        if (self::$timezone === NULL) {
               self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
         if (!self::$timezoneString) {
             self::$timezoneString = bx_helpers_config::getTimezoneAsString();
        }
        $tablePrefix = $this->tablePrefix.$this->getParameter($path,"tableprefix");
        $xml = "";
        $query = 'SELECT blogposts.post_uri,blogposts.id,
        blogposts.blog_id,
        blogposts.post_lang,
        blogposts.post_title,
        blogposts.post_uri,
        blogposts.post_content,
        blogposts.post_content_extended,
        blogposts.post_info,
        blogposts.post_status, 
        blogposts.post_guid_version,
        
        unix_timestamp(blogposts.changed) as lastmodified,
        DATE_FORMAT(DATE_ADD(blogposts.post_date, INTERVAL '. self::$timezone .' SECOND), "%d.%m.%Y %H:%i") as post_date,
        unix_timestamp(blogposts.post_date) as unixtime,
        blogposts.post_expires as expires,
        blogposts.post_comment_mode,
        DATE_FORMAT(blogposts.post_date, "%Y-%m-%dT%H:%i:%SZ") as post_date_iso,
        blogposts.post_author,
        count(blogcomments.id) as comment_count,
        unix_timestamp(max(blogcomments.changed)) as comment_lastmodified
        from '.$tablePrefix.'blogposts as blogposts left join '.$tablePrefix.'blogcomments as blogcomments on blogposts.id = blogcomments.comment_posts_id
        and blogcomments.comment_status = 1';
        
        if(isset($blogid)) {
            $query .= ' where blogposts.id = "'.$id.'" and blogposts.blog_id = "'.$blogid.'" group by blogposts.id ';
        } else {
            $query .= ' where blogposts.id = "'.$id.'" group by blogposts.id ';
        }
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->lastModified = max($this->lastModified, $row['lastmodified'], $row['comment_lastmodified']);
            
            
            
            $xml .= '<div class="entry" ';
            
            $xml .= ' id = "entry'.$row['id'].'"';
            $xml .= ' blog:blog_id="'.$row['blog_id'].'" ' ;
            
            if($blograting == 'true') {
                $rating = bx_plugins_blog_rating::getRatingById($row['id']);
                if($rating) {
                    $xml .= $rating.' ';
                } else {
                    $xml .= ' blog:rating="0" blog:myrating="0" ';
                }
            }
            
            
            if(isset($row['post_lang'])) {
                    $xml .= ' blog:blog_lang="'.$row['post_lang'].'" ' ;
            }
            $xml .= ' blog:post_uri="'.$row['post_uri'].'" ' ;
            $xml .= ' blog:post_date_iso="'.$row['post_date_iso'].'" ' ;
            $xml .= ' blog:post_status="'.$row['post_status'].'" ' ;
            $xml .= ' blog:post_comment_mode="'.$row['post_comment_mode'].'" ' ;
            $xml .= ' blog:post_guid_version="'.$row['post_guid_version'].'" ' ;
            $xml .= ' blog:post_expires="'.$row['expires'].'" ';
            if ($row['post_comment_mode'] == 99) {
                $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
            }
            $onemonthago = time() - 2678400;
            if ($row['post_comment_mode'] == 2 || ($row['post_comment_mode'] == 1 && $onemonthago < $row['unixtime'])) {
                $xml .= ' blog:post_comment_allowed="1" ';
                if ($GLOBALS['POOL']->config->blogTrackbacksTimeLimit == 'true' && $onemonthago  > $row['unixtime']) {
                    $xml .= ' blog:post_trackbacks_allowed="0" ';
                } else {
                    $xml .= ' blog:post_trackbacks_allowed="1" ';
                }
                
                $commentsAllowed = true;
            } else  {
                $xml .= ' blog:post_comment_allowed="0" ';
                $xml .= ' blog:post_trackbacks_allowed="0" ';
                $commentsAllowed = false;
            }
            
            
            if (isset($row['comment_count'])) {
                $xml .= ' blog:comment_count = "'.$row['comment_count'].'"';
            }
            
            $xml .= '>';
            $xml .= '<h2 class="post_title">'.$row['post_title'].'</h2>';
            // get categories
            if (! ($catrows = $GLOBALS['POOL']->cache->get("plugins_blog_post_categories_".$id))) { 
                $catres = $GLOBALS['POOL']->db->query("select ".$tablePrefix."blogcategories.id , fullname, fulluri from ".$tablePrefix."blogcategories
                left join ".$tablePrefix."blogposts2categories on ".$tablePrefix."blogcategories.id = ".$tablePrefix."blogposts2categories.blogcategories_id where ".$tablePrefix."blogposts2categories.blogposts_id = $id and ".$tablePrefix."blogcategories.blog_id = ".$blogid." and ".$tablePrefix."blogcategories.status=1");
                if (MDB2::isError($catres)) {
                    throw new PopoonDBException($catres);
                }
                $catrows = $catres->fetchAll(MDB2_FETCHMODE_ASSOC);
                $GLOBALS['POOL']->cache->set("plugins_blog_post_categories_".$id,$catrows,0,array("table_blogcategories","plugins_blog_id_".$id));
            }
            $xml .= '<div class="post_meta_data">';
            $xml .= '<span class="post_categories">';
            foreach ($catrows as $catrow) {
                $xml .= '<span id="cat'.$catrow['id'].'" class="post_category"><a rel="tag" href="'.BX_WEBROOT_W.$path.$catrow['fulluri'].'/">'.htmlspecialchars(html_entity_decode($catrow['fullname'],ENT_NOQUOTES,'UTF-8')).'</a></span>';
            }
            $xml .= '</span>';
            // author
            $post_author_fullname =  bx_helpers_users::getFullnameByUsername($row['post_author']);
            if ($post_author_fullname) {
                
                $xml .= '<span class="post_author"><a href="'.BX_WEBROOT_W.$path.'archive/author/'.$row['post_author'].'/">'.$post_author_fullname.'</a></span>';
            } else {
                $xml .= '<span class="post_author"><a href="'.BX_WEBROOT_W.$path.'archive/author/'.$row['post_author'].'/">'.$row['post_author'].'</a></span>';
            }
            //post date
            $xml .= '<span class="post_date">'.$row['post_date'].' ' . self::$timezoneString . '</span>';
            $xml .= '</div>';
            $xml .= '<div class="post_content">';
            
            
            $xml .= $row['post_content'];
            
            
            $xml .= '</div>';   
            
            if($doComments){
                $xml .= '<a name="post_content_extended"/><div class="post_content_extended">';

                $xml .= $row['post_content_extended'].'</div>';
            }
             $tags = bx_metaindex::getTagsById($path.$row['post_uri'].'.html');

            if (count($tags) > 0) {
                $xml .= '<div class="post_tags">Tags:';
                foreach ( $tags as $tag) {
                    $xml .= '<span class="post_tag"><a rel="tag" href="'.BX_WEBROOT_W.$path.'archive/tag/'.$tag.'/">'.$tag.'</a></span> ';
                }
                $xml .= '</div>';
                    $relatedEntries = bx_metaindex::getRelatedInfoByTags($tags,$path.$row['post_uri'].'.html');
                    if (count($relatedEntries) > 0) {
                        $count = 0;
                        foreach ($relatedEntries as $_resid => $value) {
                            if (!($value['status'] & $this->overviewPerm ) || ($value['lastModified'] && $value['lastModified'] >time())) {
                                continue;
                            } else if ($count == 0) {
                                $xml .= '<div class="post_related_entries"><i18n:text>Related Entries</i18n:text>:';
                            }
                            $count++;
                            if ($count > 5) {
                                break;
                            }
                            if (isset($value['title'])) {
                                $_restitle = $value['title'];
                            } else {
                                $_restitle = $_resid;
                            }
                            $xml .= '<div class="post_related"><a title="'.$value['resourceDescription'].'" href="'.$value['outputUri'].'">'.$_restitle.'</a></div> ';
                        }
                        if ($count > 0) {
                            $xml .= '</div>';
                        }
                    }
            }


            $xml .= '<div class="post_links">';
            $posturipath = BX_WEBROOT_LANG.substr($path,1).'archive/'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
            
            if(!$doComments){
                if(trim(($row['post_content_extended']))){
                    $xml .= '<span class="post_more"><a class="post_more" href="'.$posturipath.'#post_content_extended"><i18n:text>Read whole post</i18n:text></a></span>';
                }
            }
            

            $xml .= '<span class="post_comments_count"><a href="'.$posturipath.'#comments">'.$row['comment_count'].'</a></span> ';
            $xml .= '<span class="post_uri"><a href="'.$posturipath.'" rel="bookmark">Permalink</a></span>';


            $xml .= '</div>';
            
            //get comments
            // don't do it if doComments = 2 (for extended POsts only..)
            if ($doComments && $doComments !== 2) {
                $query = "select id, openid, comment_author, DATE_FORMAT(date_add(comment_date, INTERVAL ". self::$timezone." SECOND),'%d.%m.%Y %H:%i') as comment_date, comment_author_email, comment_type, comment_author_url, comment_content from ".$tablePrefix."blogcomments where comment_status = 1 and comment_posts_id = ".$row['id']. "
                order by ".$tablePrefix."blogcomments.comment_date";
                $cres = $GLOBALS['POOL']->db->query($query);
                if (MDB2::isError($cres)) {
                    throw new PopoonDBException($cres);
                }
                $xml .=$this->getBlogComments($cres);
                if ($commentsAllowed) {
                    $imgid = 0;
                    $perm = bx_permm::getInstance();
                    if (!$perm->isLoggedIn()) {
            $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
                        $isCaptcha = bx_helpers_captcha::isCaptcha($days, $row['post_date']);
                    } else {
                        $isCaptcha = false;
                    }

                    //if captcha is active
                    if($isCaptcha == true) {
                        // generate captcha
                        $imgid = bx_helpers_captcha::doCaptcha();
                    }
                    $xml .= $this->getCommentForm($emailBodyID = '', $posturipath, $imgid, $isCaptcha);
                } else {
                    $xml .= '<div class="comments_not"><i18n:text i18n:key="blogNoNewComments">No new comments allowed (anymore) on this post.</i18n:text></div>';

                }

            }
            $xml .= "<blog:info xmlns='http://bitflux.org/doctypes/blog'>".$row['post_info'].'</blog:info>';

            $xml .= '</div>';

        }
            
        return $xml;
    }

    protected function getBlogPosts($res, $path, $doComments = false) {


        $xml = "";
        if(!MDB2::isError($res)) {
            if ($res->numRows() === 0) {
                throw new BxPageNotFoundException(substr($_SERVER['REQUEST_URI'],1));
            }
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $xml.= $this->getBlogPostData($row['id'], $path, $doComments);
            }

        }
        return $xml;

    }


    public function stripRoot() {
        return false;
    }

    public function isRealResource($path, $id) {

        return true;
    }

    public function getChildren($uri,$id) {
        return array();
        //this could be an implementation for getChildren
        // but I decided to do it with a portlet ;)
        $query = "select * from blogcategories order by cat_name";
        $res = $GLOBALS['POOL']->db->query($query);
        $ch = array();
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $reso = new bx_resources_simplecollection($row['cat_name']);
            $reso->props['display-order'] = 1;
            $ch[] = $reso;
        }
        return $ch;
    }


    protected function getBlogComments($res) {
        if (!self::$timezoneString) {
                 self::$timezoneString = bx_helpers_config::getTimezoneAsString();
        }
        if(!MDB2::isError($res)) {
            $d = new domdocument();
            $xml = '<a name="comments"/><div class="comments">';
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                //$this->lastModified = max ($this->lastModified,$row['lastmodified']);
                $sxml = '<div class="comment" ';
                $sxml .= ' id = "'.$row['id'].'"';
                $sxml .= '>';
                $sxml .= '<a name="comment'.$row['id'].'"/>';
                $sxml .= '<div class="comment_meta_data">';
                
                $sxml .= '<span class="comment_author_email">'.$row['comment_author_email'].'</span>';
                $sxml .= '<span class="comment_author">';
                if ($row['comment_author_url']) {
                    $sxml .= '<a href="';
            if (strpos($row['comment_author_url'],'http:') !== 0) {
                $sxml .= 'http://';
            }
            $sxml .= $row['comment_author_url'].'">'.$row['comment_author'].'</a></span>';
                } else {
                    $sxml .= $row['comment_author'].'</span>';
                }
                $sxml .= '<span class="comment_date">'.$row['comment_date'].' '. self::$timezoneString . '</span>';
                if($row['openid'] == 1) {
                    $sxml .= '<img class="openid" src="'.BX_WEBROOT.'webinc/images/openid.gif"/>';
                }
                
                $sxml .= '<span class="comment_type">'.$row['comment_type'].'</span>';
                $sxml .= '</div>';
                $sxml .= '<div class="comment_content">';

                $sxml .= bx_helpers_string::makeLinksClickable($row['comment_content']).'</div>';
                $sxml .= "</div>";
                                if (!$d->loadXML($sxml)) {
                    $d->recover = true;
                    $d->loadXML($sxml);
                    $sxml = $d->saveXML($d->documentElement);
                    $d->recover = false;
                }
                $xml .= $sxml;
                
            }
            $xml .= '</div>';

        }
        return $xml;

    }


    public function getResourceTypes() {
        //        return array('blogpost');
        return array();
    }

    public function getResourceById($path, $id, $mock = false) {
        
        
        $pathid = $path.$id;
        if (!isset($this->res[$pathid])) {
            
            if ($row = $GLOBALS['POOL']->cache->get("plugins_blog_path_".$pathid)) {
                
            } else {
                $id = str_replace(".html","",$id);
                
                $tablePrefix = $this->tablePrefix.$this->getParameter($path,"tableprefix");
                $db = $GLOBALS['POOL']->db;
                if (self::$timezone === NULL) {
                    self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
                }
                
                $query = "SELECT id, post_title, concat(DATE_FORMAT(blogposts.post_date,'%Y/%m/%H/'),
                blogposts.post_uri) as permalink,
                blogposts.post_status as status,
                unix_timestamp(date_add(blogposts.post_date, INTERVAL ". self::$timezone." SECOND)) as cdate
                FROM ".$tablePrefix."blogposts as blogposts where post_uri = ". $db->quote($id);
                $r = $db->query($query);
                $row = $r->fetchRow(MDB2_FETCHMODE_ASSOC);
                $GLOBALS['POOL']->cache->set("plugins_blog_path_".$pathid,$row,0,"plugins_blog_id_".$row['id']);
            }
            $res = new bx_resources_simple($pathid);
            if ($row['id'] > 0) {
                $res->props['title'] = $row['post_title'];
                $res->props['outputUri'] = BX_WEBROOT_W.$path.'archive/'.$row['permalink'].'.html';
                $res->props['resourceDescription'] = "Blog Post";
                $res->props['creationdate'] = $row['cdate']    ;
                $res->props['lastmodified'] = $row['cdate'] ;
                $res->props['status'] = $row['status'];
                $this->res[$pathid] = $res;
            } else {
                $this->res[$pathid] = null;
            }
            
        }
        return $this->res[$pathid];
    }

    public function getAddResourceParams() {
        return false;
    }

    public function addResource($name, $parentUri, $options=array(),$resourceType = null) {

        $type = (isset($options['type'])) ? $options['type'] : "xhtml";
        $res = null;

        switch($type) {

            case "blogpost":
            $res = new bx_resources_text_blogpost($id, true);
            break;

        }

        if (is_object($res)) {
            return $res->addResource($name, $parentUri, $options);
        }


        return false;

    }

    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }

    static function getTreeInstance($tablePrefix) {
        if (!(self::$tree)) {
            self::$tree = new SQL_Tree($GLOBALS['POOL']->db);
            self::$tree->idField = "id";
            self::$tree->referenceField = "parentid";
            self::$tree->tablename = $tablePrefix."blogcategories";
            self::$tree->FullPath = "fulluri";
            self::$tree->Path = "uri";
            self::$tree->Title = "name";
        }
        // print_r(self::$tree);
        return self::$tree;
    }
    
    public function getOverviewSections($path,$mainOverview) {
        $perm = bx_permm::getInstance();

        $sections = array();
        $dom = new bx_domdocs_overview();
        $dom->setTitle("Blog");
        $dom->setPath($path);
        $dom->setType("blog");
        $dom->setIcon("blog");

        if($perm->isAllowed('/blog/',array('blog-back-post'))) {
             $dom->addLink("Make new Blog Entry",'edit'.$path."newpost.xml");
             $dom->addLink("Blog Posts Overview / Latest Comments",'edit'.$path);
        }    

        $dom->addTab("Edit Categories/Links");
        if($perm->isAllowed($path,array('blog-back-categories'))) {
            $dom->addLink("Edit Categories",'edit'.$path.'sub/categories/');
        }
        if($perm->isAllowed($path,array('blog-back-blogroll'))) {
            $dom->addLink("Edit Links and Linkcategories",'edit'.$path.'sub/blogroll/');
        }
        if($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-blogcomments'))) {
            $dom->addLink("Edit Comments",'dbforms2/blogcomments/');
        }
        
         if($perm->isAllowed($path,array('blog-back-sidebars'))) {
            $dom->addLink("Edit Sidebars",'edit'.$path.'sub/sidebar/');
        }
        
        
        //if (!$mainOverview) {
            $dom->addTab("RSS");
            $dom->addLink("RSS Feed",BX_WEBROOT_W.$path."rss.xml");
            $dom->addLink("RSS Comments",BX_WEBROOT_W.$path."latestcomments.xml");
            $ah = bx_helpers_perm::getAccessHash();
        if($perm->isAllowed('/blog/',array('blog-back-private'))) {
            $dom->addLink("RSS Feed (incl. private)",BX_WEBROOT_W.$path."rss.xml?ah=$ah");
            $dom->addLink("RSS Comments (incl. private)",BX_WEBROOT_W.$path."latestcomments.xml?ah=$ah");
            $dom->addLink("Generate new private key","javascript:if (confirm('Are you sure you want to generate a new private key? \n All your RSS feeds for private posts will change.')) {location.href='".BX_WEBROOT."admin/webinc/generatenewaccesshash/?path=".$path."'}");
        }
            $dom->addTab("Etc");
        if($perm->isAllowed('/blog/',array('blog-back-post'))) {
            $dom->addLink("Flux CMS Bookmarklet","javascript:%20var%20baseUrl%20=%20'".BX_WEBROOT."admin/edit/blog/newpost.xml?';%20var%20url=baseUrl;var%20title=document.title;%20url=url%20+%20'link_title='%20+%20encodeURIComponent(title);%20var%20currentUrl=document.location.href;%20url=url%20+%20'&link_href='%20+%20encodeURIComponent(currentUrl);%20var%20selectedText;%20selectedText=getSelection();%20if%20(selectedText%20!=%20'')%20url=url%20+%20'&text='%20+%20encodeURIComponent(selectedText);var win = window.open(null, '', 'width=700,height=500,scrollbars,resizable,location,toolbar');win.location.href=url;win.focus();"
            ,"Drag'n'drop to your bookmarks for immediate posting from your browser");
        }
            
        //}
        return $dom;
    }
    
    protected function getCommentForm($emailBodyID = '', $posturipath, $imgid = null, $isCaptcha) {        
        
        $remember = null;
        $data = $this->commentData;
        if($data == null) {
            $data['name'] = null;
            $data['openid_url'] = null;
            $data['email'] = null;
            $data['comments'] = null;
        }
        
        //get TablePrefix
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        
        if(!empty($_SESSION['flux_openid_url']) || !empty($_COOKIE['openid_enabled'])) {
            $query = "select comment_author, comment_author_email, comment_author_url from ".$tablePrefix."blogcomments where comment_author_url = ".$GLOBALS['POOL']->db->quote($_SESSION['flux_openid_url'])." or  comment_author_url = ".$GLOBALS['POOL']->db->quote($_COOKIE['openid_enabled'])." order by id DESC LIMIT 1";
            $res = $GLOBALS['POOL']->db->query($query);
            $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
            $data['name'] = $row['comment_author'];
            $data['openid_url'] = $row['comment_author_url'];
            $data['email'] = $row['comment_author_email'];
        } else {
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
        }
        
          
        $xml = '<div class="comments_new">';
         
         
        if ($this->newCommentError) {
            $xml .= '<p style="color:red;">'.$this->newCommentError.'</p>';
        }
            $xml .= '<form name="bx_foo" action="'.$posturipath.'#commentform" method="post">
               <table class="form" style="margin-left:25px;" border="0" cellspacing="0" cellpadding="0" id="commentform">
               <tr>
               <td valign="top"><i18n:text i18n:key="blogCommentName">Name</i18n:text>*</td>
               <td class="formHeader" valign="middle"><input class="formgenerell" type="text" name="name" id="name" value="'.$data['name'].'"/></td>
               </tr><tr>
               <td valign="top"><i18n:text i18n:key="blogCommentEmail">E-Mail</i18n:text></td>
               <td><input class="formgenerell" type="text" name="email" id="email" value="'.$data['email'].'"/></td>
               </tr>
                <tr><td valign="top" width="90" class="formurl">For Spammers Only</td><td valign="middle" class="formurl"><input type="text" name="url" value="" class="formurl" /></td></tr>
             
               <tr>
               <td valign="top"><i18n:text i18n:key="blogCommentURL">URL</i18n:text></td>
               <td>
                    <input class="formgenerell" type="text" id="openid_url" name="openid_url" value="'.$data['openid_url'].'"/>
                    <input type="hidden" id="verified" name="verified" value="0" />';
                    if ($GLOBALS['POOL']->config->openIdEnabled == 'true') {
                        if(isset($_SESSION['flux_openid_verified']) && $_SESSION['flux_openid_verified'] == true) {
                            $xml .= '<input id="verify" onclick="return openIdSubmit()" style="background-image:url(/webinc/images/openid.gif);  background-repeat:no-repeat;" type="button" value="&#160;&#160;&#160;&#160;Ok" />';
                        } else {
                            $xml .= '<input id="verify" onclick="return openIdSubmit()" style="background-image:url(/webinc/images/openid.gif);  background-repeat:no-repeat;" type="button" value="&#160;&#160;&#160;&#160;Verify" />';
                        }
                        $xml .= '<div id="openIdVerify" style="display:none;">trusted</div>';
                    }
               $xml .= '</td>
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
               <td><textarea rows="10" cols="40" name="comments" id="comments">'.$data['comments'].'</textarea></td>
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
                  
                if($isCaptcha == 1) {
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
                <input onclick="javascript:previewSubmit(this.parentNode);" type="button" i18n:attr="value"  value="Preview" class="formbutton" />
                
                </td>
                </tr>
               </table>
               </form>
               </div>
               ';
        $this->setJavaScriptSource('webinc/js/prototype.lite.js');
        $this->setJavaScriptSource('webinc/js/moo.ajax.js');
        
        return $xml;
    }
    
    public function handlePublicPost($path,$id, $data) {
        
        if (!empty($data['email'])) {
            $data['email'] = htmlspecialchars(strip_tags($data['email'] ));
        }
        if (!empty($data['name'])) {
            $data['name'] = htmlspecialchars(strip_tags($data['name']));
        } 
        
        if (!empty($data['openid_url'])) {
            $data['openid_url'] = htmlspecialchars(strip_tags($data['openid_url']));
        }
        
        $error = bx_plugins_blog_handlecomment::handlePost($path,$id,$data);
        $this->commentData = $data;
        if ($error) {
            $this->newCommentError = $error;
        } else {
            $this->newCommentError = false;
        }
    }
    
        
    protected function getSidebarData($root) {
        $query = "SELECT sidebar, name, content, isxml FROM ".$this->tablePrefix."sidebar AS sidebar WHERE sidebar != '0' order by sidebar,position";
        $res = $GLOBALS['POOL']->db->query($query);
    if ($GLOBALS['POOL']->db->isError($res)) {
        return;
    }
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $s = $root->appendChild($root->ownerDocument->createElement("sidebar"));
            $s->setAttribute("sidebar",$row['sidebar']);
            $s->setAttribute("name",$row['name']);
            if ($row['isxml']) {
                $node = bx_helpers_xml::getFragment($row['content'],$root->ownerDocument);
                if ($node->firstChild) {
                    $s->appendChild($node);
                } else {
                    $s->appendChild($root->ownerDocument->createTextNode($row['content']));
                    $row['isxml'] = 0;
                }
            } else {
                $s->appendChild($root->ownerDocument->createTextNode($row['content']));
            }
            $s->setAttribute('isxml',$row['isxml']);
        }
        
        
        
        
    }
    
    
}



?>
