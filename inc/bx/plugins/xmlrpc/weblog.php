<?php
class bx_plugins_xmlrpc_weblog extends bx_plugins_xmlrpc {
    
    static private $instance = array();
    static protected $timezone = NULL;
    protected function __construct() {
        if (!defined('BX_WEBROOT_W')) {
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            if ($lang != BX_DEFAULT_LANGUAGE) {
                define ('BX_WEBROOT_W', BX_WEBROOT.$lang);
            } else {
                define ('BX_WEBROOT_W',substr( BX_WEBROOT,0,-1));
            }
            
        }
        
        
    }
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_xmlrpc_weblog::$instance[$mode])) {
            bx_plugins_xmlrpc_weblog::$instance[$mode] = new bx_plugins_xmlrpc_weblog($mode);
        } 
        return bx_plugins_xmlrpc_weblog::$instance[$mode];
    }   
    
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        if ($name.'.'.$ext == "xmlrpc.rsd") {
            return "xmlrpc.rsd";
        } else {
            return "xmlrpc.xml";
        }
    }
    
  
    function registerFunctions() {
      $this->addDispatch("metaWeblog.getRecentPosts","getRecentPosts");
        $this->addDispatch("mt.getRecentPostTitles","getRecentPostTitles");
        $this->addDispatch("metaWeblog.newMediaObject","newMediaObject");
        $this->addDispatch("mt.getCategoryList","getCategoryList");
        $this->addDispatch("mt.getPostCategories","getPostCategories");
        $this->addDispatch("mt.setPostCategories","setPostCategories");
        $this->addDispatch("metaWeblog.editPost","editPost");
        $this->addDispatch("metaWeblog.newPost","newPost");
        $this->addDispatch("blogger.editPost","editPostBlogger");
        $this->addDispatch("blogger.newPost","newPostBlogger");
        
        $this->addDispatch("blogger.deletePost","deletePost");
         $this->addDispatch("metaWeblog.getPost","getPost");
         $this->addDispatch("blogger.getUsersBlogs","getUsersBlogs");
         
    }
     function getRecentPostTitles($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
        $count = $params->params[3]->getval();
        bx_global::registerStream("blog");
        if (!$count) {$count = 10;}
        $sxe = simplexml_load_file('blog://'.$this->path.'entries('.$count.').xml');
        $foo = array();
        $z = 1;
        foreach($sxe->entry as $entry) {
           $foo[]= $this->getPostStruct($entry);
           if ($z >= $count) {
                break;
            }
            $z++;
        }
        $r = new XML_RPC_Value($foo,"array");
        return new XML_RPC_Response ($r);;
    }
    
    function getRecentPosts($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
        $count = $params->params[3]->getval();
        bx_global::registerStream("blog");
        if (!$count) {$count = 10;}
        $sxe = simplexml_load_file('blog://'.$this->path.'entriesfull('.$count.').xml');
        $foo = array();
        $z = 1;
        foreach($sxe->entry as $entry) {
           $foo[]= $this->getPostStruct($entry);
           if ($z >= $count) {
                break;
            }
            $z++;
        }
        $r = new XML_RPC_Value($foo,"array");
        return new XML_RPC_Response ($r);;
    }
    
     public function getContentById($path, $id) {
         if ($id == "xmlrpc.rsd") {
            $xml = '<?xml version="1.0" ?> 
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd" >
    <service>
        <engineName>Flux CMS (Movable Type Compatible)</engineName> 
        <engineLink>http://www.flux-cms.org/</engineLink>
        <homePageLink>'.BX_WEBROOT_W.$path.'</homePageLink>
        <apis>
                <api name="MetaWeblog" preferred="false" apiLink="'.BX_WEBROOT_W.$path.'xmlrpc.xml" blogID="1" />
        </apis>
    </service>
</rsd>';

        $dom = new domdocument();
        $dom->loadXML($xml);
        return $dom;
            
        } else {

        
            return parent::getContentById($path,$id);
        }
           
     }
    
     function getPost($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        bx_global::registerStream("blog");
        $id = $params->params[0]->getval();
        $sxe = simplexml_load_file("blog://".$this->path."_id".$id.".xml");
        $r = $this->getPostStruct($sxe);
        return new XML_RPC_Response($r);
    }
   
    
    function getPostStruct ($entry) {
        
            $content = $entry->xpath("atom:content");
            if (isset($content[0])) {            
                $content = preg_replace("#<atom:content[^>]*>#","",$content[0]->asXML());
                $content = str_replace("</atom:content>","",html_entity_decode(str_replace("&lt;","&amp;lt;",$content),ENT_NOQUOTES,'UTF-8'));
                $content = str_replace('xmlns:blog="http://www.flux-cms.org/doctypes/blog"',"",$content);
            } else {
                $content = "";
            }
            
            $content_extended = $entry->xpath("atom:content_extended");
            if (isset($content_extended[0])) {            
                $content_extended = preg_replace("#<atom:content_extended[^>]*>#","",$content_extended[0]->asXML());
                $content_extended = str_replace("</atom:content_extended>","",html_entity_decode(str_replace("&lt;","&amp;lt;",$content_extended),ENT_NOQUOTES,'UTF-8'));
                $content_extended = str_replace('xmlns:blog="http://www.flux-cms.org/doctypes/blog"',"",$content_extended);
            } else {
                $content_extended = null;
            }
          
            $date = str_replace("+00:00",":00Z",$entry->created);
            
            
            if ($tags = trim($entry->tags)) {
                if (strpos($_SERVER['HTTP_USER_AGENT'],"ecto") !== false) {
                    $tags = bx_metaindex::splitTags($tags);
                    $keywords = "<!-- technorati tags start -->";
                        foreach ($tags as $tag) {
                            $keywords .= "<a href=\"http://technorati.com/tag/$tag\" rel=\"tag\">$tag</a> ";
                        }
                        $keywords .= "<!-- technorati tags end -->";
                } else {
                    $keywords = $tags;
                }
            } else {
                $keywords = "";
            }
                
                
            
            
            $ar = array (
            "userid" => $entry->author->name, 
            "dateCreated" => $date, 
            "postid" => $entry->id, 
            
            "title" => $entry->title,
            "mt_keywords" => $keywords,
            
            );
            
            if ($content) {
              $ar['description'] = $content;   
              $ar['link'] = BX_WEBROOT_W.$this->path.$entry->uri.'.html';
              $ar['permaLink'] = BX_WEBROOT_W.$this->path.$entry->uri.'.html';
            }
            
            if ($content_extended) {
                $ar['mt_text_more'] = $content_extended;   
            }
            
            foreach ($ar as $key => $value) {
                if ($key == "dateCreated") {
                    $xmlrpcar[$key] = new XML_RPC_Value($value,"dateTime.iso8601");
                } else {
                    $xmlrpcar[$key] = new XML_RPC_Value($value,"string");
                }
            }
            
            
            return new XML_RPC_Value($xmlrpcar,"struct");
        
    }
    
    function newMediaObject($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
        $val = $params->params[3]->getval();
        $dir = dirname($val['name']);
        if ($dir == ".") {
            $dir = '/files/';
        } else {
            $dir = '/files/'. $dir . '/';
        }
        $base = bx_helpers_string::makeUri(basename($val['name']),true);
        bx_helpers_file::mkpath(BX_OPEN_BASEDIR.$dir);
        
        file_put_contents(BX_OPEN_BASEDIR.$dir.$base, $val['bits']);
          return new XML_RPC_Response (new XML_RPC_Value(BX_WEBROOT_W.str_replace("//","/",$dir.$base)));;
    }
    
    function getCategoryList($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
         bx_global::registerStream("blog");
         $dom = new DomDocument();
         $dom->load("blog://".$this->path."categories.xml");
         $xp = new DomXPath($dom);
         $res = $xp->query("/*/dc:subject");
         $cats = array();
         foreach($res as $sub) {
             $st = array();
            $st['categoryId'] = new XML_RPC_Value(str_replace("cat","",$sub->getAttributeNs("http://www.w3.org/XML/1998/namespace","id")));
            $st['categoryName'] = new XML_RPC_Value($sub->nodeValue);
            $cats[] = new XML_RPC_Value($st,"struct");
         }
         $r = new XML_RPC_Value($cats,"array");
        return new XML_RPC_Response ($r);;
        
    }
    
    function getPostCategories($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
         bx_global::registerStream("blog");
         $tablePrefix = bx_streams_blog::getTablePrefix($this->path);
        $id = $params->params[0]->getval();
        $catres = $GLOBALS['POOL']->db->query("select blogcategories.id , fullname, fulluri from ".$tablePrefix."blogcategories as blogcategories
        left join ".$tablePrefix."blogposts2categories as blogposts2categories on blogcategories.id = blogposts2categories.blogcategories_id where blogposts2categories.blogposts_id = $id ");
        $cats = array();   
        while ($catrow = $catres->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $st = array();
            $st['categoryId'] = new XML_RPC_Value($catrow['id']);
            $st['categoryName'] = new XML_RPC_Value($catrow['fullname']);
            $st['isPrimary'] = new XML_RPC_Value(false,'bool');
            $cats[] = new XML_RPC_Value($st,"struct");
        }                
        
        $r = new XML_RPC_Value($cats,"array");
        return new XML_RPC_Response ($r);;
    }
    
    function setPostCategories ($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
        $id = $params->params[0]->getval();
        $cats = array();
        $isId = false;
        foreach ( $params->params[3]->getval() as $value) {
            if (isset($value['categoryName'])) {
                $cats[] = $value['categoryName']->getval();
            } else {
                $cats[] = $value['categoryId']->getval();
                $isId = true;
            }
               
        }
        bx_streams_blog::updateCategoriesDirect($id,$cats,$isId,bx_streams_blog::getTablePrefix($this->path));
        
         return new XML_RPC_Response (new XML_RPC_Value(true,"boolean"));;
    }
    
    function editPostBlogger($params) {
   
        array_shift($params->params);
        
        return $this->editPost($params);
        
    }
    
    function editPost($params) {
        if (!$this->checkAuth($params,1)) {
            return false;
        }
        
        
         $id = $params->params[0]->getval();
         $content = $params->params[3]->getval();
         if (!is_array($content)) {
             
             $_c = $content;
             $content = array();
             $content['description'] = $_c;
             $content['title'] = '';
             
             
         }
         
         if (!isset($content['mt_keywords'])) {
            $content['mt_keywords'] = '';
         }
             
             
         bx_global::registerStream("blog");
         
         
               $fd = fopen("blog://".$this->path."_id".$id,"w");
         
            
            fwrite($fd, '<entry xmlns="http://purl.org/atom/ns#">');
            fwrite($fd, '<title>'.htmlspecialchars(html_entity_decode($content['title'],ENT_NOQUOTES,'UTF-8')).'</title>');
            $tags = html_entity_decode($content['mt_keywords'],ENT_NOQUOTES,'UTF-8');
            $tags = strip_tags($tags);
            fwrite($fd, '<tags>'.$tags.'</tags>');
            fwrite($fd, '<id>'.$id.'</id>');
            //  fwrite($fd, '<uri>'.$data['uri'].'</uri>');
            if (isset($content['dateCreated'])) {
                fwrite($fd, '<created>'.$content['dateCreated'].'</created>');
            } else {
                fwrite($fd, '<created keep="true"></created>');
            }
            if (isset($content['categories'])) {    
                foreach ($content['categories'] as $p) {
                    $cat = $p->getval();
                   fwrite ($fd,'<sa-cat:categories  xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sa-cat="http://sixapart.com/atom/category#"><dc:subject>'.$cat.'</dc:subject></sa-cat:categories>');
                }                   
            }
            
            fwrite($fd, '<atom:content type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.bx_helpers_string::tidyfy(stripslashes($content['description'])).'</atom:content>');
            if (isset($content['mt_text_more']) && trim($content['mt_text_more'])) {
                fwrite($fd, '<atom:content_extended type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.bx_helpers_string::tidyfy(stripslashes($content['mt_text_more'])).'</atom:content_extended>');
            }

            fwrite($fd, '</entry>');
            fclose($fd);
            
        return new XML_RPC_Response (new XML_RPC_Value(true,"boolean"));;
    }
    
    function deletePost($params) {
        if (!$this->checkAuth($params,2)) {
            return false;
        }
        $id = $params->params[1]->getval();
        bx_streams_blog::deleteEntryDirect($id,$this->path);
          return new XML_RPC_Response (new XML_RPC_Value(true,"boolean"));;
    }
    
    function newPostBlogger($params) {
   
        array_shift($params->params);
        
        return $this->newPost($params);
        
    }
    
    function newPost ($params) {
          if (!$this->checkAuth($params,1)) {
            return false;
        }
        $id = $params->params[0]->getval();
         $content = $params->params[3]->getval();
         if (!is_array($content)) {
             $_c = $content;
             $content = array();
             $content['description'] = $_c;
             $content['title'] = '';
         }
         if (!isset($content['mt_keywords'])) {
            $content['mt_keywords'] = '';
         }
             
         bx_global::registerStream("blog");
         if (trim($content['title']) == '') {
             $content['title'] = 'No Title';
         }
         $uri = bx_streams_blog::getUniqueUri(bx_helpers_string::makeUri(trim($content['title'])),$this->path);
         
   
            $fd = fopen("blog://".$this->path."newpost.xml","w");
         
            fwrite($fd, '<entry xmlns="http://purl.org/atom/ns#">');
            fwrite($fd, '<title>'.htmlspecialchars(html_entity_decode($content['title'],ENT_NOQUOTES,'UTF-8')).'</title>');
            fwrite($fd, '<uri>'.$uri.'</uri>');
            $tags = html_entity_decode($content['mt_keywords'],ENT_NOQUOTES,'UTF-8');
            $tags = strip_tags($tags);
            fwrite($fd, '<tags>'.$tags.'</tags>');
            
            if (isset($content['dateCreated'])) {
                fwrite($fd, '<created>'.$content['dateCreated'].'</created>');
            } else {
                fwrite($fd, '<created ></created>');
            }
            
            if (isset($content['categories'])) {    
                foreach ($content['categories'] as $p) {
                    $cat = $p->getval();
                   fwrite ($fd,'<sa-cat:categories  xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sa-cat="http://sixapart.com/atom/category#"><dc:subject>'.$cat.'</dc:subject></sa-cat:categories>');
                }                   
            } else if (stripos($_SERVER['HTTP_USER_AGENT'],"Flickr") !== false) {
                fwrite ($fd,'<sa-cat:categories  xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sa-cat="http://sixapart.com/atom/category#"><dc:subject>__default</dc:subject></sa-cat:categories>');
            } else if (stripos($_SERVER['QUERY_STRING'],'rnd') !== false) {
                //writely...
                fwrite ($fd,'<sa-cat:categories  xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sa-cat="http://sixapart.com/atom/category#"><dc:subject>__default</dc:subject></sa-cat:categories>');
                
            }
            fwrite($fd, '<atom:content type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.bx_helpers_string::tidyfy(stripslashes($content['description'])).'</atom:content>');
            if (isset($content['mt_text_more']) && trim($content['mt_text_more'])) {
                fwrite($fd, '<atom:content_extended type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.bx_helpers_string::tidyfy(stripslashes($content['mt_text_more'])).'</atom:content_extended>');
            }
            fwrite($fd, '</entry>');
            fclose($fd);
            return new XML_RPC_Response (new XML_RPC_Value(bx_streams_blog::getIdByUri($uri,$this->path)));
    }
    
    function getUsersBlogs($params) {
         if (!$this->checkAuth($params,1)) {
            return false;
        }
     $ar = array();
     $ar['url'] = new XML_RPC_Value(BX_WEBROOT_W.$this->path.'xmlrpc.xml');
     $ar['blogid'] = new XML_RPC_Value(1);
     $blogname = $GLOBALS['POOL']->config['blogname'];
     if (!$blogname) {
	     $blogname = $GLOBALS['POOL']->config['sitename'];
     }
     $ar['blogName'] = new XML_RPC_Value($blogname);
     return new XML_RPC_Response(new XML_RPC_Value(array(new XML_RPC_Value($ar,"struct")),"array"));
    }

    protected function checkAuth($params,$start = 1) {
        //fake pearauth
        $_POST['username'] = $params->params[$start]->getval();
        $_POST['password'] = $params->params[$start+1]->getval();
       $conf = bx_config::getInstance();
            
        @session_start();        
         $confvars = $conf->getConfProperty('permm');
         $permObj = bx_permm::getInstance($confvars);
         
        if ($permObj instanceof bx_permm) {
           
            if (!$permObj->isAllowed('/',array('admin'))) {   
                $e = new Exception("Invalid login",1);
                throw $e;
            } else {
                return true;
            }
        }   
    }
}
