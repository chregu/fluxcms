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

    /*** magic methods and functions ***/

    public static function getInstance($mode) {
        if (!isset(bx_plugins_blog::$instance[$mode])) {
            bx_plugins_blog::$instance[$mode] = new bx_plugins_blog($mode);
        }
        return bx_plugins_blog::$instance[$mode];
    }

    protected function __construct($mode) {
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
        if ($perm->isLoggedIn()) {
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

        if ($mode == "rss") {
            $id = "index";
        }
        
        $query = "SELECT ".$tablePrefix."blogposts.id from ".$tablePrefix."blogposts ";
        
        $archivepath = "";
        $archivewhere = "";
        $total = 0;
        $gmnow = gmdate("Y-m-d H:i:s",time());
        if (isset($_GET['q']) && !(strpos($_SERVER['REQUEST_URI'], '/search/') === 0)) {
            $cat = "";
            $query .=" where (MATCH (post_content,post_title) AGAINST ('" . $_GET['q'] ."') or  post_title like '%" .  $_GET['q']  . "%') and ".
            $tablePrefix."blogposts.post_status & ".$this->overviewPerm;
            $query .= " and blog_id = ".$blogid;
            $query .= " and post_date < '".$gmnow."'";
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
                
                $archivewhere .= " and post_date < '".$gmnow."'";
                
                $catAllOnly = $GLOBALS['POOL']->config->getConfProperty('blogPostsExpireCatAllOnly');
                if ($cat == "" || $catAllOnly == "false") {
                    $archivewhere .= " AND ";
                    $archivewhere .= $tablePrefix."blogposts.post_expires = '0000-00-00 00:00:00' OR ";
                    $archivewhere .= "unix_timestamp(".$tablePrefix."blogposts.post_expires) >= ".time();
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
            $xml .= " :: " . $catname;
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

        if (!@$dom->loadXML($xml)) {
            //if it didn't work loading, try with replacing ampersand
            //FIXME: DIRTY HACK, works only in special cases..
            $xml = str_replace("&amp;","§amp;",$xml);
            $xml = preg_replace("#\&([^\#])#", "&#38;$1", $xml);
            $xml = str_replace("§amp;","&amp;",$xml);
            $dom->loadXML($xml);
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

       if ( !$res || $db->isError($res) ) {
           return false;
       } else {
           $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
           return 'archive/'. date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
       }

   }
    protected function getBlogPostData($id,$path,$doComments = false) {
        //if test is 1 delete all captcha files in dynimages
        $test = rand(1,500);
        if($test <= 10) {
            
            $dir = BX_PROJECT_DIR.'dynimages/captchas/';
            $opendir = opendir($dir);
            while (false !== ($file = readdir($opendir))) {
                if ($file != "." && $file != "..") {
                    if (filectime($dir.$file) < time()-(20*60)) {
                        @unlink($dir.$file);
                    } else {
                        continue;
                    }
                }
            }
            closedir($opendir);
        }
        
        $blogid = $this->getParameter($path,"blogid");
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
            $catres = $GLOBALS['POOL']->db->query("select ".$tablePrefix."blogcategories.id , fullname, fulluri from ".$tablePrefix."blogcategories
            left join ".$tablePrefix."blogposts2categories on ".$tablePrefix."blogcategories.id = ".$tablePrefix."blogposts2categories.blogcategories_id where ".$tablePrefix."blogposts2categories.blogposts_id = $id and ".$tablePrefix."blogcategories.blog_id = ".$blogid." and ".$tablePrefix."blogcategories.status=1");
            if (MDB2::isError($catres)) {
                throw new PopoonDBException($catres);
            }
            $xml .= '<div class="post_meta_data">';
            $xml .= '<span class="post_categories">';
            while ($catrow = $catres->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $xml .= '<span id="cat'.$catrow['id'].'" class="post_category"><a rel="tag" href="'.BX_WEBROOT_W.$path.$catrow['fulluri'].'/">'.$catrow['fullname'].'</a></span>';
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
                $xml .= '<div class="post_content_extended">';

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
            $posturipath = BX_WEBROOT_W.$path.'archive/'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
            
            if(!$doComments){
                if(trim(($row['post_content_extended']))){
                    $xml .= '<span class="post_more"><a class="post_more" href="'.$posturipath.'"><i18n:text>Read whole post</i18n:text></a></span>';
                }
            }
            

            $xml .= '<span class="post_comments_count"><a href="'.$posturipath.'#comments">'.$row['comment_count'].'</a></span> ';
            $xml .= '<span class="post_uri"><a href="'.$posturipath.'">Permalink</a></span>';


            $xml .= '</div>';
            
            
            //get comments
            // don't do it if doComments = 2 (for extended POsts only..)
            if ($doComments && $doComments !== 2) {
                $query = "select id, comment_author, DATE_FORMAT(date_add(comment_date, INTERVAL ". self::$timezone." SECOND),'%d.%m.%Y %H:%i') as comment_date, comment_author_email, comment_type, comment_author_url, comment_content from ".$tablePrefix."blogcomments where comment_status = 1 and comment_posts_id = ".$row['id']. "
                order by ".$tablePrefix."blogcomments.comment_date";
                $cres = $GLOBALS['POOL']->db->query($query);
                if (MDB2::isError($cres)) {
                    throw new PopoonDBException($cres);
                }
                $xml .=$this->getBlogComments($cres);
                if ($commentsAllowed) {
                    $imgid = 0;
                    $captchacontrol = 0;
                    $missingfields = false;
                    $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
                    $isCaptcha = bx_helpers_captcha::isCaptcha($days, $row['post_date']);
                    if(isset($_POST['bx_fw']['name']) && isset($_POST['bx_fw']['comments'])) {
                    //add some more data and clean some others
                        $data['remote_ip'] = $_SERVER['REMOTE_ADDR'];
                        $data['name'] = strip_tags($_POST['bx_fw']['name'] );
                        $data['email'] = strip_tags($_POST['bx_fw']['email'] );
                        $data['comments'] = $_POST['bx_fw']['comments'];
                        $data['remember'] = $_POST['bx_fw']['comment_remember'];
                        $data['base'] = strip_tags($_POST['bx_fw']['url'] );
                        $data['passphrase'] = $_POST['passphrase'];
                        $data['imgid'] = $_POST['imgid'];
                        $data['comment_notification'] = $_POST['bx_fw']['comment_notification'];
                        
                    } else {
                        $data['name'] = null;
                        $data['base'] = null;
                        $data['email'] = null;
                        $data['comments'] = null;
                        $data['passphrase'] = null;
                        $data['imgid'] = null;
                    }
                    //if captcha is active
                    if($isCaptcha == true) {
                        $captchacontrol = true;
                        // generate captcha
                        $imgid = bx_helpers_captcha::doCaptcha();
                        //checks if name and comment is set if not missingfield is true
                        if($data['name'] && $data['comments']) {
                            //checks captcha
                            if (!$this->checkCaptcha($data['passphrase'], $data['imgid'])) {
                                //captcha failed
                                $captchacontrol = false;
                            } else {
                                //captcha works
                                $captchacontrol = true;
                                //make comment
                                $this->handlePublicPost($path, $id, $data, $imgid);
                            }
                            //no missing field(s)
                            $missingfields = false;
                        } else {
                            //checks if something is posted or not
                            if($data['name'] || $data['comments'] || $data['email'] || $data['base']) {
                                //missing field(s)
                                $missingfields = true;
                            }
                        }
                    } else {
                        //comment without captcha
                        $captchacontrol = true;
                        //checks if name and comment is set if not missingfield is true
                        if($data['name'] && $data['comments']) {
                            $this->handlePublicPost($path, $id, $data);
                            $missingfields = false;
                        } else {
                            //checks if something is posted or not
                            if($data['name'] || $data['comments'] || $data['email'] || $data['base']) {
                                $missingfields = true;
                            }
                        }
                    }
                    $xml .= $this->getCommentForm($emailBodyID = '', $posturipath, $imgid, $isCaptcha, $captchacontrol, $data, $missingfields);
                    /*$xml .='<div class="comments_new"><forms:formwizard  xmlns:forms="http://bitflux.org/forms" src="xml/blogcomment.xml">
                    <forms:parameter name="id" value="'.$row['id'].'" type="noform"/>
                    </forms:formwizard></div>';*/
                    
                } else {
                    $xml .= '<div class="comments_not"><i18n:text>No new comments allowed (anymore) on this post.</i18n:text></div>';

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

    public function isRealResource() {

        return true;
    }

    public function getChildren() {
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


    public function getResourceTypes() {
        //        return array('blogpost');
        return array();
    }

    public function getResourceById($path, $id, $mock = false) {
        
        
        $pathid = $path.$id;
        if (!isset($this->res[$pathid])) {
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

        $sections = array();
        $dom = new bx_domdocs_overview();
        $dom->setTitle("Blog");
        $dom->setPath($path);
        $dom->setType("blog");
        $dom->setIcon("blog");
        $dom->addLink("Make new Blog Entry",'edit'.$path."newpost.xml");

        $dom->addLink("Blog Posts Overview / Latest Comments",'edit'.$path);
        $dom->addTab("Edit Categories/Links");
        $dom->addLink("Edit Categories",'edit'.$path.'sub/categories/');
        $dom->addLink("Edit Links and Linkcategories",'edit'.$path.'sub/blogroll/');
        $dom->addLink("Edit Comments",'dbforms2/blogcomments/');
        //if (!$mainOverview) {
            $dom->addTab("Diversa");
            $dom->addLink("Flux CMS Bookmarklet","javascript:%20var%20baseUrl%20=%20'".BX_WEBROOT."admin/edit/blog/newpost.xml?';%20var%20url=baseUrl;var%20title=document.title;%20url=url%20+%20'link_title='%20+%20encodeURIComponent(title);%20var%20currentUrl=document.location.href;%20url=url%20+%20'&link_href='%20+%20encodeURIComponent(currentUrl);%20var%20selectedText;%20selectedText=getSelection();%20if%20(selectedText%20!=%20'')%20url=url%20+%20'&text='%20+%20encodeURIComponent(selectedText);var win = window.open(null, '', 'width=700,height=500,scrollbars,resizable,location,toolbar');win.location.href=url;win.focus();"
            ,"Drag'n'drop to your bookmarks for immediate posting from your browser");
        //}
        return $dom;
    }
    
    protected function getCommentForm($emailBodyID = '', $posturipath, $imgid = null, $isCaptcha, $captchacontrol, $data = null, $missingfields) {
        //only works per post atm
        if (isset($_COOKIE['fluxcms_blogcomments'])) {
           foreach ($_COOKIE['fluxcms_blogcomments'] as $name => $value) {
               $data[$name] = $value;
           }
        }
        $xml = '<div class="comments_new">';
        if($data == null) {
            $data['name'] = null;
            $data['url'] = null;
            $data['email'] = null;
            $data['comments'] = null;
        }
        
        if($captchacontrol == false) {
            $xml .= '<p style="color:red;">Captcha Number is not correct pls try again</p>';
        }
        if($missingfields == true) {
            $xml .= '<p style="color:red;">Please fill in your name and comment</p>';
        }
        $xml .= '<form name="bx_foo" action="'.$posturipath.'" method="post">
               <table class="form" style="margin-left:25px;" border="0" cellspacing="0" cellpadding="0">
               <tr>
               <td valign="top">Name*</td>
               <td class="formHeader" valign="middle"><input class="formgenerell" type="text" name="bx_fw[name]" value="'.$data['name'].'"/></td>
               </tr><tr>
               <td valign="top">E-Mail</td>
               <td><input class="formgenerell" type="text" name="bx_fw[email]" value="'.$data['email'].'"/></td>
               </tr><tr>
               <td valign="top">URL</td>
               <td><input class="formgenerell" type="text" name="bx_fw[url]" value="'.$data['base'].'"/></td>
               </tr><tr>
               <td valign="top">Comment*</td>
               <td><textarea name="bx_fw[comments]">'.$data['comments'].'</textarea></td>
               </tr><tr>
               <td colspan="2" valign="top"><input type="checkbox" name="bx_fw[comment_notification]" />
               Notify me via E-Mail when new comments are made to this entry</td>
                </tr><tr>
               <td colspan="2" valign="top"><input type="checkbox" name="bx_fw[comment_remember]" />
               Remember me (need cookies)</td>
                </tr>';
                
                if($isCaptcha == 1) {
                    $xml .= '<tr>
                    <td colspan="2"><br/>Anti-Spam Überprüfung (Code ins Eingabefeld übertragen)</td>
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
                <td><br /><input type="submit" name="send" value="Send" class="formbutton" /></td>
                </tr>
               </table>
               </form>
               </div>
               ';
        return $xml;
    }
    
    public function handlePublicPost($path,$id, $data, $imgid) {
        if($data['remember']) {
                if (isset($_COOKIE['fluxcms_blogcomments'])) {
                    setcookie("fluxcms_blogcomments[name]", 0, "/");
                    setcookie("fluxcms_blogcomments[email]", 0, "/");
                    setcookie("fluxcms_blogcomments[base]", 0, "/");
                }
                if($data['name']) {
                    setcookie("fluxcms_blogcomments[name]", $data['name'], time()+30*24*60*60, '/');
                }
                
                if($data['email']) {
                    setcookie("fluxcms_blogcomments[email]", $data['email'], time()+30*24*60*60, '/');
                }
                
                if($data['base']) {
                    setcookie("fluxcms_blogcomments[base]", $data['base'], time()+30*24*60*60, '/');
                }
        }
        
        $timezone = bx_helpers_config::getTimezoneAsSeconds();
        $isok = false;
        
        foreach($data as $name => $value) {
            $data[$name] = bx_helpers_string::utf2entities(str_replace("&","&amp;",trim($value)));
        }
/*

FIXME: can't set cookies, due to the location redirect at the end...
if (isset($data['comment_remember'])) {
            $remember = array('name' => $data['name'],'email' => $data['email'],'base' => $data['base'],'comment_notify' => @$data['comment_notify'],'comment_remember' => $data['comment_remember']);
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
        where blogposts.id = "'.$id.'" ';
        $res = $GLOBALS['POOL']->db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        if(isset($data['captcha'])) {
            if (!$this->checkCaptcha($data['captcha'], $data['imgid'])) {
                return false;
            }
        }
        
        if ($row['post_comment_mode'] == 99) {
            $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
        }
        if (!($row['post_comment_mode'] == 2 || ($row['post_comment_mode'] == 1 && (time() - 2678800) < $row['unixtime']))) {
            die("aaaNo comments allowed anymore...");
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
        
        /*$screenNode = $this->parent->confctxt->query("/bxco:wizard/bxco:screen[@emailTo]");
        $screenNode = $screenNode->item(0);*/
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
        $data['comments'] = preg_replace("#\<(/[^(".$_tags,"&lt;$1", $data['comments']);
        $data['comments'] = preg_replace("#\<([^(/|".$_tags,"&lt;$1", $data['comments']);
        $allowedTagsString = "<".implode("><",$this->allowedTags).">";
        if ($tidy) {
            $tidy->parseString(strip_tags(nl2br($data['comments']),$allowedTagsString ),$this->tidyOptions,"utf8");
            $tidy->cleanRepair();
            $data['comments'] = popoon_classes_externalinput::basicClean((string) $tidy);
            // and tidy it again 
            $tidy->parseString($data['comments']);
            $tidy->cleanRepair();
            $data['comments'] = (string) $tidy;
        } else {
            $data['comments'] =  popoon_classes_externalinput::basicClean(strip_tags(nl2br($data['comments']),$allowedTagsString));
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
        
        if (in_array(strtolower(preg_replace("#[^a-z]#i","",$data['name'])),$this->knownspammers)) {
            $commentRejected .= "* Known spammer name: " . $data['name'] ."\n";
            $deleteIt = true;
        }*/
        
        
        /* If url field is filled in, it was a bot ...*/
        if (isset($data['url']) && $data['url'] != "") {
            $commentRejected .= "* URL field was not empty, assuming bot: " . $data['url']."\n";        
            $deleteIt = true;
        }
        /* Max 5 links per post and SURBL check */
        if (preg_match_all("#http://[\/\w\.\-]+#",$data['comments'], $matches) || $data['base'] != '') {
            if ($data['base'] != '') {
                $matches[0][] = $data['base'] ;
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
        }
        $query = 'insert into '.$blogTablePrefix.'blogcomments (comment_posts_id, comment_author, comment_author_email, comment_author_ip,
        comment_date, comment_content,comment_status, comment_notification, comment_notification_hash,
        comment_author_url         
        ) VALUES ("'.$row['id'].'",'.$db->quote($data['name'])
        .','.$db->quote($data['email'],'text').','.$db->quote($data['remote_ip']).',"'.gmdate('c').'",'.$db->quote(bx_helpers_string::utf2entities($data['comments'])).','.$comment_status.','.$db->quote($data['comment_notification']).',"'.$comment_notification_hash.'",'.$db->quote($data['base'],'text').')';
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        $GLOBALS['POOL']->dbwrite->loadModule('Extended'); 
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
                if ($GLOBALS['POOL']->config->lastdbversion >= 5266) {
                    $hash = md5($lastID . rand().microtime(true));
                    $query = 'update '.$blogTablePrefix.'blogcomments set comment_hash = ' . $GLOBALS['POOL']->db->quote($hashPrefix . $hash) . ' where id = ' . $lastID; 
                    $GLOBALS['POOL']->dbwrite->query($query);
                    $data['accepturi'] .= " ".BX_WEBROOT.'admin/webinc/approval/?hash='.$hashPrefix.$hash;  
                } else {
                    $data['accepturi'] .= " Please update your Flux CMS DB to use that feature.";
                }
                $data['edituri'] = BX_WEBROOT.'admin/edit/blog/sub/comments/?id='.$lastID;
                $emailSubject .= "New comment on '" . html_entity_decode($row['post_title'],ENT_QUOTES,'ISO-8859-1') . "'";
                
                //$bodyID = $screenNode->getAttribute('emailBodyID');
                
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
                    $this->parent->_replaceTextData($emailBody, $data);
                    $emailBody = html_entity_decode($emailBody,ENT_QUOTES,'UTF-8');
                } else {
                    foreach ($data as $key => $value) {
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
                
                header ('Location: '. bx_helpers_uri::getLocationUri($row["post_uri"]) . '.html?sent='.time().'#comment');
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
            return FALSE;
    }
    
    protected function checkCaptcha($captcha, $imgid) {
        $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
        $magickey = $GLOBALS['POOL']->config->magicKey;
        preg_match("#.*.html#", $_SERVER['REQUEST_URI'], $matches);
        
        
        if($imgid == md5($captcha.floor(time()/(60*15)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0']) or $imgid == md5($captcha.floor(time()/(60*15-1)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0'])) {
            return true;
        } else {
            return false;
        }
    }
}



?>
