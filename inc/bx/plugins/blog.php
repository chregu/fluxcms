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
    public $maxPosts = 10;

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
            $xml =  call_user_func(array($plugin,"getContentById"), $path, $id, $params,null,$tablePrefix);
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
            $query .= " and post_date < '".$gmnow."'";
            $doComments = false;
            $total = $GLOBALS['POOL']->db->query($query)->fetchOne(0);

            $query .= " order by post_date desc limit ".$startEntry . ",10";
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

            $query .= 'order by post_date DESC limit '.$startEntry . ','.$this->maxPosts;
            
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
            $_r = $GLOBALS['POOL']->db->query("select fullname from ".$tablePrefix."blogcategories where fulluri = '$cat'");
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
                $end =   (($startEntry + 10) > $total) ? $total : $startEntry + 10;
                $xml .= '<div class="blog_pager" blog:start="'.($startEntry + 1).'" blog:end="'.$end.'" blog:total="'.$total.'">';

                $xml .= '<span class="blog_pager_prevnext">';

                $path = BX_WEBROOT_W  .$path.$archivepath.$cat;
                if (substr($path,-1) != "/") {$path .= "/";}
                if (isset($_GET['q'])) {
                    $searchAdd = "&amp;q=".$_GET['q'];
                } else {
                    $searchAdd = "";
                }
                if ($startEntry >= 10) {
                    $xml .='<a href="./?start='.($startEntry -10).$searchAdd.'"><i18n:text>Prev</i18n:text></a>';
                }
                if ($startEntry < ($total - 10)) {
                    $xml .=' <a href="./?start='.($startEntry + 10).$searchAdd.'"><i18n:text>Next</i18n:text></a>';
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
        if (self::$timezone === NULL) {
               self::$timezone = bx_helpers_config::getTimezoneAsSeconds();
        }
         if (!self::$timezoneString) {
             self::$timezoneString = bx_helpers_config::getTimezoneAsString();
        }
        $tablePrefix = $this->tablePrefix.$this->getParameter($path,"tableprefix");
        $xml = "";
        $query = 'SELECT blogposts.post_uri,blogposts.id,
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
        and blogcomments.comment_status = 1
        where blogposts.id = "'.$id.'" group by blogposts.id ';

        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->lastModified = max($this->lastModified, $row['lastmodified'], $row['comment_lastmodified']);
            
            
            
            $xml .= '<div class="entry" ';
            $xml .= ' id = "entry'.$row['id'].'"';
            $xml .= ' blog:post_uri="'.$row['post_uri'].'" ' ;
            $xml .= ' blog:post_date_iso="'.$row['post_date_iso'].'" ' ;
            $xml .= ' blog:post_status="'.$row['post_status'].'" ' ;
            $xml .= ' blog:post_comment_mode="'.$row['post_comment_mode'].'" ' ;
            $xml .= ' blog:post_guid_version="'.$row['post_guid_version'].'" ' ;
            $xml .= ' blog:post_expires="'.$row['expires'].'" ';
            if ($row['post_comment_mode'] == 99) {
                $row['post_comment_mode'] = $GLOBALS['POOL']->config->blogDefaultPostCommentMode;
            }

            if ($row['post_comment_mode'] == 2 || ($row['post_comment_mode'] == 1 && (time() - 2678400) < $row['unixtime'])) {
                $xml .= ' blog:post_comment_allowed="1" ';
                $commentsAllowed = true;
            } else  {
                $xml .= ' blog:post_comment_allowed="0" ';
                $commentsAllowed = false;
            }
            if (isset($row['comment_count'])) {
                $xml .= ' blog:comment_count = "'.$row['comment_count'].'"';
            }
            
            $xml .= '>';

            $xml .= '<h2 class="post_title">'.$row['post_title'].'</h2>';
            // get categories
            $catres = $GLOBALS['POOL']->db->query("select ".$tablePrefix."blogcategories.id , fullname, fulluri from ".$tablePrefix."blogcategories
            left join ".$tablePrefix."blogposts2categories on ".$tablePrefix."blogcategories.id = ".$tablePrefix."blogposts2categories.blogcategories_id where ".$tablePrefix."blogposts2categories.blogposts_id = $id and ".$tablePrefix."blogcategories.status=1");
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
                                $xml .= '<div class="post_related_entries">Related Entries:';
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
                    $xml .='<div class="comments_new"><forms:formwizard  xmlns:forms="http://bitflux.org/forms" src="xml/blogcomment.xml">
                    <forms:parameter name="id" value="'.$row['id'].'" type="noform"/>
                    </forms:formwizard></div>';
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
            $dom->addLink("Flux CMS Bookmarklet","javascript:%20var%20baseUrl%20=%20'".BX_WEBROOT."/admin/edit/blog/newpost.xml?';%20var%20url=baseUrl;var%20title=document.title;%20url=url%20+%20'link_title='%20+%20encodeURIComponent(title);%20var%20currentUrl=document.location.href;%20url=url%20+%20'&link_href='%20+%20encodeURIComponent(currentUrl);%20var%20selectedText;%20selectedText=getSelection();%20if%20(selectedText%20!=%20'')%20url=url%20+%20'&text='%20+%20encodeURIComponent(selectedText);var win = window.open(null, '', 'width=700,height=500,scrollbars,resizable,location,toolbar');win.location.href=url;win.focus();"
            ,"Drag'n'drop to your bookmarks for immediate posting from your browser");
        //}
        return $dom;
    }

}



?>
