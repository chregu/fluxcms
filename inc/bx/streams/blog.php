<?php

class bx_streams_blog extends bx_streams_buffer {
    
    static $tablePrefixes = array();
    
    static $adminPlugins = array("plazes","geoloc");
        
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
    function contentOnRead($path) {
        
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
        $p = $p['plugin'];
        $colluri = $parts['coll']->uri;
        $this->tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix().$p->getParameter($colluri,"tableprefix");

        if ($pos = strpos($parts["name"],"(")) {
                $pos2 = strpos($parts["name"],")");
                $params = substr($parts["name"],$pos+1, $pos2 - $pos - 1);
                $parts["name"] = substr($parts["name"],0,$pos);
                $params = explode(",",$params);
       }
        $pos = strpos($parts["name"],"/");
        $appendPostComments = FALSE;
        if ($parts['ext'] == "xml" && $pos === false) {
             $section = $parts["name"];
             $name = "";
        }
        else if ($pos > 0) {
            $section = substr($parts["name"],0,$pos);
            $name = substr($parts["name"],$pos+1,0);
        } else {
            $section = "";
            $name = $parts["name"];
        }
        switch ($section) {
            case "categories":
                return $this->returnCategories();
            
            case "entries":
                if (isset($params) && isset($params[0])) {
                    $p->maxPosts= $params[0];
                } else {
                    $p->maxPosts= 10;
                }
                $xml = $p->getContentById("$colluri","_all/index");
                $xsl = new DomDocument();
                $xsl->load(BX_LIBS_DIR."/streams/blog/html2feed.xsl");
            break;        
            case "entriesfull":
                if (isset($params) && isset($params[0])) {
                    $p->maxPosts= $params[0];
                } else {
                    $p->maxPosts= 10;
                }
                $xml = $p->getContentById("$colluri","_all/index");
                $xsl = new DomDocument();
                $xsl->load(BX_LIBS_DIR."/streams/blog/html2feedfull.xsl");
            break;        
            default:
                try {
                    $xml = $p->getContentById("$colluri",$parts["name"]);
                    $appendPostComments = TRUE;
                } catch (BxPageNotFoundException $e) {
                    $xml = new DomDocument();
                    $xml->load(BX_LIBS_DIR."/streams/blog/newentry.xml");
                    $xp = new DomXPath($xml);
                    $res = $xp->query("/atom:entry/atom:author/atom:name/text()");
                    
                    if ($res->length > 0 ) {
                        try {
                            $res->item(0)->data = bx_permm::getInstance()->getUsername();
                        } catch(Exception $e) {
                            $res->item(0)->data = '';
                        }
                    }
                    
                  
                    
                    if (isset($_GET['link_title'])) {
                        $res = $xp->query("/atom:entry/atom:title");
                        
                        $res->item(0)->appendChild($xml->createTextNode(($_GET['link_title'])));
                        $res = $xp->query("/atom:entry/atom:uri");
                        
                        $res->item(0)->appendChild($xml->createTextNode(bx_helpers_string::makeUri(bx_helpers_globals::stripMagicQuotes($_GET['link_title']))));
                    }   
                    if (isset($_GET['text'])) {
                        $res = $xp->query("/atom:entry/atom:content");
                        $html = "<foo><blockquote>".nl2br(htmlspecialchars(bx_helpers_globals::stripMagicQuotes($_GET['text'])))."</blockquote>";
                        
                        if (isset($_GET['link_href'])) {
                            $html .= '<p>From <a href="'.htmlspecialchars(bx_helpers_globals::stripMagicQuotes($_GET['link_href'])).'">' .htmlspecialchars(bx_helpers_globals::stripMagicQuotes($_GET['link_title'])) .'</a></p>'; 
                        }
                        $html .= "</foo>";
                        
                        $domi = new domdocument();
                        $domi->loadXML($html);
                        foreach ($domi->documentElement->childNodes as $node) {
                            $res->item(0)->appendChild($xml->importNode($node,true));
                        
                        }
                        
                    }
                      
                    return $xml->saveXML();
                }
                $xsl = new DomDocument();
                $xsl->load(BX_LIBS_DIR."/streams/blog/html2xml.xsl");
                
        }
        
        $proc = new XSltProcessor();
        $proc->importStylesheet($xsl);
        $proc->setParameter("","webroot",BX_WEBROOT);
        $proc->setParameter("","colluri",$colluri);
        $xml = $proc->transformToDoc($xml);
         
        // append latest comments to generated feed xml when we are in overview mode
        if($section == 'entries') {
            $latestCommentsXML = $p->getContentByID("$colluri", 'plugin=comments(latest).xml');
            $latestCommentsNode = $xml->importNode($latestCommentsXML->documentElement, TRUE);
            $new = $xml->documentElement->appendChild($latestCommentsNode);
            $new->setAttribute("status","1");
            
            $latestCommentsXML = $p->getContentByID("$colluri", 'plugin=comments(latest,2).xml');
            $latestCommentsNode = $xml->importNode($latestCommentsXML->documentElement, TRUE);
            $new = $xml->documentElement->appendChild($latestCommentsNode);
            $new->setAttribute("status","2");
            
            $latestCommentsXML = $p->getContentByID("$colluri", 'plugin=comments(latest,3).xml');
            $latestCommentsNode = $xml->importNode($latestCommentsXML->documentElement, TRUE);
            $new = $xml->documentElement->appendChild($latestCommentsNode);
            $new->setAttribute("status","3");

        } else if($appendPostComments === TRUE) {
            // append all comments of a post when viewing an existing entry
            $idNS = $xml->documentElement->getElementsByTagName('id');
            $entryID = $idNS->item(0)->nodeValue;

            $commentsXML = $p->getContentByID($colluri, 'plugin=comments('.$entryID.',0).xml');
            $commentsNode = $xml->importNode($commentsXML->documentElement, TRUE);
            $xml->documentElement->appendChild($commentsNode);
        }
        
        return $xml->saveXML();
    }
    
    
    function getAttrib($name) {
        
        return $this->getParameter($name);
    }
    
    function stream_close() {

        $this->tablePrefix = self::getTablePrefix($this->path);
        
        if ($this->mode == 'w') {
            $content = $this->html;
            $content = preg_replace('/(\.\.\/+)+files/', BX_WEBROOT.'files', $content);
            $this->dom = new DomDocument();
            if (!$this->dom->loadXML($content)) {
                
                // try fixing html entities
                $content = html_entity_decode(str_replace("&amp;","&amp;amp;",$content),ENT_NOQUOTES,"UTF-8");
                // and set evil recover to true (5.1 only)
                $this->dom->recover = true;
                if (!$this->dom->loadXML($content)) {
                    print "<font color='red'>Couldn't save. Invalid XML Document....</font>";
                    return false;
                }
            }
            $this->xp = new domxpath($this->dom);
            $this->xp->registerNamespace("atom","http://purl.org/atom/ns#");
            $this->xp->registerNamespace("xhtml","http://www.w3.org/1999/xhtml");
            $this->xp->registerNamespace("dc","http://purl.org/dc/elements/1.1/");
            $id =$this->getElement("id");
            /*if ($this->path != '/'.$id.'.html') {
                $this->deleteEntry(substr($path,1,-5));
            }*/
            if ($id) {
                $this->updateEntry($id);   
            } else {
                $this->insertEntry();
            }
          
        }
        return true;
    }
    
    function deleteEntry($id) {
        $query = "delete from ".$this->tablePrefix."blogposts where post_uri = '$id'";
        $GLOBALS['POOL']->dbwrite->query("$query");
    }
    
    function fixDate($date, $defNow=1) {
        
        if (!$date || $date == "now()") {
            return gmdate("Y-m-d H:i:s",time());
        }
        
        $date =  preg_replace("/([0-9])T([0-9])/","$1 $2",$date);
        $date =  preg_replace("/([\+\-][0-9]{2}):([0-9]{2})/","$1$2",$date);
        $date = strtotime($date);
        
        if ($date <= 0) {
            return  gmdate("Y-m-d H:i:s",time());
        } 
        
        return  gmdate("Y-m-d H:i:s",$date);
    }
    
    function getPostObject() {
        
        //it's defined at the end of this file
        $post = new bx_streams_blog_post();
        $post->title = $this->getElement("title",true);
        $post->content = $this->getElement("content",true);
        $post->content_extended = $this->getElement("content_extended",true);
        $post->summary = $this->getElement("summary",true);
        $post->status = $this->getElement("status");
        $post->comment_mode = $this->getElement("comment_mode");
         
        if (!$post->comment_mode) {
             $post->comment_mode = 99;
        }
         
        $post->uri = $this->getElement("uri");
        $post->tags = bx_metaindex::splitTags(trim($this->getElement("tags")));
        $post->trackbacks = $this->getElement("trackback");
        $post->autodiscovery = $this->getElement("autodiscovery");
        $post->status = $this->getElement("status");
        if (!$post->status) {
            $post->status = 1;
        }
        $post->date = $this->fixDate($this->getElement("created"));
        $post->expires = $this->fixDate($this->getElement("expires"));
        $post->post_info = "";
        return $post;
    }
    
    function insertEntry($id = null) {
       $db = $GLOBALS['POOL']->db;
       $dbwrite = $GLOBALS['POOL']->dbwrite;
        $post = $this->getPostObject();
        if ($id) {
            $post->id = $id;
        } else {
            $post->id = $dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
        }
        try {
            $post->author = bx_permm::getInstance()->getUsername(); 
        } catch (Exception $e) {
            $post->author = $this->getElement('author'); 
            if(!$post->author ) {
                $post->author = "unknown";
            }
        }
        
        foreach (self::$adminPlugins as $plugin) {
            $post = call_user_func(array("bx_plugins_blog_".$plugin,"onInsertNewPost"),$post);
        }
         
        $query = "insert into ".$this->tablePrefix."blogposts 
            (id, post_author, post_date, post_expires, post_title, post_content, post_content_extended, post_uri, post_info, post_status, post_comment_mode) values 
            ($post->id, 
            ".$db->quote($post->author,'text').", 
            '".$post->date."',
            '".$post->expires."',
            ".$db->quote(bx_helpers_string::utf2entities($post->title),'text').",
            ".$db->quote(bx_helpers_string::utf2entities($post->content),'text').",
            ".$db->quote(bx_helpers_string::utf2entities($post->content_extended),'text').",
            ".$db->quote($post->uri,'text').",
            ".$db->quote(bx_helpers_string::utf2entities($post->getInfoString()),'text').",
            ".$db->quote($post->status).",
            ".$db->quote($post->comment_mode)."
            )";
        $res = $dbwrite->query($query);
        if (MDB2::isError($res)) {
            
            if ($res->code == -5) {
                //check if id really already exists or if we have another problem...
                $query = "select id from ".$this->tablePrefix."blogposts where id = ".$post->id;
                $resid = $dbwrite->query($query);
                if ($resid->numRows() > 0) {
                    //first set it to the max value of this table
                    $resid = $dbwrite->query("select max(id) from blogposts");
                    $maxid = $resid->fetchOne(0);
                    if ($maxid > $post->id) {
                        //this is maybe mysql only... I don't know, how sequences are stored in other DBs
                        //shouldn't really matter as the while loop after it just takes longer otherwise
                        $dbwrite->querywrite("update ".$this->tablePrefix."_sequences_seq set sequence = $maxid");
                    }
                    //then loop through it, until we find an id, which fits
                    while(MDB2::isError($res)) {
                        //use global tableprefix for sequences for that
                        $post->id = $GLOBALS['POOL']->dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
                         $query = "insert into ".$this->tablePrefix."blogposts 
                         (id, post_author, post_date, post_title, post_content, post_uri) values
                         ($post->id, '".bx_permm::getInstance()->getUsername()."', '".$post->date."', ".$db->quote($post->title).",".$db->quote($post->content).",".$db->quote($post->uri).")";
                        $res = $dbwrite->query($query);
                    }
                }
                
                else {
                    
                    throw new PopoonDBException($res);
                }  
                
            } else {
                throw new PopoonDBException($res);
            }
        }
        
        $post->uri  = bx_collections::sanitizeUrl(dirname($this->path)).$post->uri.'.html';
        
        bx_metaindex::setTags($post->uri,bx_metaindex::implodeTags($post->tags),true);
        bx_resourcemanager::setProperty($post->uri,"subject",bx_metaindex::implodeTags($post->tags),'http://purl.org/dc/elements/1.1/');
        bx_resourcemanager::setProperty($post->uri,"title",$post->title,'bx:');
        bx_resourcemanager::setProperty($post->uri,"content",$post->content,'bx:');
        
        
        $this->updateCategories($post->id);
        
        if ($post->status == 1) {
            $this->weblogsPing();
        }
        if($post->autodiscovery){
           $this->getRemotePage($post->title,$post->content,$post->uri);    
        }
        $this->sendTrackbacks($post->trackbacks,$post->title,$post->content,$post->uri);
        
    }
    
    function weblogsPing() {
        
        if ( $GLOBALS['POOL']->config->getConfProperty("noOutsideConnections") != "true") {
        /*$servicesExtended = array("http://rpc.pingomatic.com/","http://planet.freeflux.net/ping/");
        $servicesOld = array("http://rpc.technorati.com/rpc/ping");*/
        
        $servicesExtended = bx_helpers_config::getProperty("blogWeblogsPing",true);
        if ($fixed =  trim(bx_helpers_config::getProperty("blogWeblogsPingFixed",true))) {
            $servicesExtended[] = $fixed;
        }
        $blogname = bx_helpers_config::getProperty("blogname"); 
        if (!$blogname) {
            $blogname = bx_helpers_config::getProperty("sitename");
        }
        $url = BX_WEBROOT. substr(bx_collections::getCollectionUri($this->path),1);
        include_once("XML/RPC.php");        
        $rpcName = new XML_RPC_Value($blogname, 'string');
        $rpcUrl= new XML_RPC_VALUE($url,'string');
        $rpcCheckUrl = new XML_RPC_VALUE($url,'string');
        $rpcRssUrl= new XML_RPC_VALUE($url.'rss.xml','string');
        
        $params = array($rpcName, $rpcUrl, $rpcCheckUrl, $rpcRssUrl);
        $msg = new XML_RPC_Message('weblogUpdates.extendedPing', $params);
        foreach ($servicesExtended as $host) {
            $parts = parse_url($host);
            $cli = new XML_RPC_Client($parts['path'], $parts['host']  );
            $resp = $cli->send($msg, 5);
            
            if (!$resp) {
                error_log( 'WeblogsPing: Communication error: ' . $cli->errstr);
                continue;
            }
            if (!$resp->faultCode()) {
                error_log('WeblogsPing: Pinging to ' . $host . ' succeeded');
                //return true;
            } else {
                error_log("WeblogsPing: Pinging $host didn't work :  " . $resp->faultCode() . " " . $resp->faultString());
            }
        }
      /*
        $params = array($rpcName, $rpcUrl);
        $msg = new XML_RPC_Message('weblogUpdates.ping', $params);

        foreach ($servicesOld as $host) {
            $parts = parse_url($host);
            $cli = new XML_RPC_Client($parts['path'], $parts['host']  );
            $resp = $cli->send($msg, 5);
            
            if (!$resp) {
                error_log( 'WeblogsPing: Communication error: ' . $cli->errstr);
                continue;
                
            }
            
            if (!$resp->faultCode()) {
                error_log('WeblogsPing: Pinging to ' . $host . ' succeeded');
                //return true;
            } else {
                error_log("WeblogsPing: Pinging $host didn't work :  " . $resp->faultCode() . " " . $resp->faultString());
            }
        }*/
        }
    
    }
    
    function updateEntry($id) {
        $db = $GLOBALS['POOL']->dbwrite;
        $post = $this->getPostObject();
       
        $post->id = $id;
        
        $row = $db->queryRow("select post_info, post_status from ".$this->tablePrefix."blogposts where id = $id", null, MDB2_FETCHMODE_ASSOC);
        if (!is_array($row)) {
            $this->insertEntry($id);
        }
        if ($row['post_info']) {
            $post->info = new domdocument();
            $post->info->loadXML('<info>'.$row['post_info'].'</info>');
        }
        $post->status_old = $row['post_status'];
        
        foreach (self::$adminPlugins as $plugin) {
             
            $func = array("bx_plugins_blog_".$plugin,"onUpdatePost");
            if (is_callable($func)) {
                $post = call_user_func($func,$post);
            }
        }
        
        $query = "update ".$this->tablePrefix."blogposts set post_title = ".$db->quote(bx_helpers_string::utf2entities($post->title)).",".
        "post_content = ".$db->quote(bx_helpers_string::utf2entities($post->content)) .",".
        "post_content_extended = ".$db->quote(bx_helpers_string::utf2entities($post->content_extended)) .",".
        "post_content_summary = ".$db->quote(bx_helpers_string::utf2entities($post->summary)) .",".
        "post_status = ". $db->quote($post->status) .",".
        "post_expires = ".$db->quote($post->expires).",".
        "post_comment_mode = ". $db->quote($post->comment_mode);
        if ($post->date) {
            $query .= ", post_date = ".$db->quote($post->date);
        }
        if ($post->uri) {
            $query .= ", post_uri = ".$db->quote($post->uri);
        } else {
            $post->uri = self::getUriById($post->id);
        }
        if ($post->info instanceof DomDocument) {
            $query .= ", post_info = ".$db->quote(bx_helpers_string::utf2entities($post->getInfoString()),'text');
        }
        $query .= " where id = '".$post->id."'";
        $res = $db->query($query);

        if($post->autodiscovery){
           $this->getRemotePage($post->title,$post->content,$post->uri);    
        }
        $this->sendTrackbacks($post->trackbacks,$post->title,$post->content,$post->uri);
        
        $post->uri  = bx_collections::sanitizeUrl(dirname($this->path)).$post->uri.'.html';
        bx_metaindex::setTags($post->uri, bx_metaindex::implodeTags($post->tags),true);
        bx_resourcemanager::setProperty($post->uri,"subject",bx_metaindex::implodeTags($post->tags),'http://purl.org/dc/elements/1.1/');
        //update categories
        bx_resourcemanager::setProperty($post->uri,"title",$post->title,'bx:');
        bx_resourcemanager::setProperty($post->uri,"content",$post->content,'bx:');
        $this->updateCategories($post->id);
        if ($post->status == 1 && $post->status_old != 1) {
            $this->weblogsPing();
        }
       
    }
    
    
    function updateCategories($id) {
        $this->xp->registerNamespace("sa-cat","http://sixapart.com/atom/category#");
        $res = $this->xp->query("/atom:entry/sa-cat:categories");
        if ($res->length > 0) {
            $res = $this->xp->query("/atom:entry/sa-cat:categories/dc:subject");
            
            //get category ids
            $cats = array();
            
            foreach($res as $cat) {
                $cats[] = $cat->nodeValue;
            }
            self::updateCategoriesDirect ($id , $cats, false, $this->tablePrefix);
        }
    }
    
    static function deleteEntryDirect($id,$path) {
        $tablePrefix = self::getTablePrefix($path);
        $query = "delete from ".$tablePrefix."blogposts where id = '$id'";
        $GLOBALS['POOL']->dbwrite->query("$query");
        $query = "delete from ".$tablePrefix."blogposts2categories where blogposts_id = '$id'";
        $GLOBALS['POOL']->dbwrite->query("$query");
        $query = "delete from ".$tablePrefix."blogcomments where  comment_posts_id = '$id'";
        $GLOBALS['POOL']->dbwrite->query("$query");
        return true;
    }
    
    static function updateCategoriesDirect ($id , $cats, $isId= false , $tablePrefix = null) {
        /*if (!$tablePrefix) {
            $tablePrefix = self::getTablePrefix($this->path);
        }*/
        if(!$isId) {
            
            if (count($cats) == 0) {  
                $ids = array();
            } else {
                
                // if ($cat[0] == "__default") {
                    // search for default cat
                // } else {
                    //search in fullname
                    $query = "select id from ".$tablePrefix."blogcategories  where fullname in ('".bx_helpers_string::utf2entities(implode("','",$cats))."')";
                    $res = $GLOBALS['POOL']->db->query($query);
                    $ids = $res->fetchCol();
                    // if no ids found, try with uri
                    if (count($ids) == 0) {
                        $query = "select id from ".$tablePrefix."blogcategories  where uri in ('".bx_helpers_string::utf2entities(strtolower(implode("','",$cats)))."')";
                        $res = $GLOBALS['POOL']->db->query($query);
                        $ids = $res->fetchCol();
                    }
                // } // end else
                // if still not found, and its $cats[0] contains  "moblog"... or __default
                if (count($ids) == 0 && ($cats[0] == '__default' || strpos(strtolower($cats[0]),"moblog") !== false )) {
                    //search for a cat containing moblog as well in title or category
                    $query = "select id from ".$tablePrefix."blogcategories  where fullname like '%moblog%' or uri like '%moblog%' LIMIT 1";
                    $res = $GLOBALS['POOL']->db->query($query);
                    $ids = $res->fetchCol();
                }
                // if still not found, just take the default one
                if (count($ids) == 0 ) {
                    // take the default one, if there is one
                    //TODO
                    // otherwise the first one
                    $query = "select id from ".$tablePrefix."blogcategories  where uri != 'root' order by l LIMIT 1";
                    $res = $GLOBALS['POOL']->db->query($query);
                    $ids = $res->fetchCol();
                }
            }
        } else {
            $ids = $cats;
        }
        //delete not choosen ids
        if (count($ids) > 0) {
            $query = "delete from ".$tablePrefix."blogposts2categories where blogposts_id = $id and not( blogcategories_id in (".implode(",",$ids)."))";
            $res = $GLOBALS['POOL']->dbwrite->query($query);
            
            //get old categories
            
            $query = "select blogcategories_id from ".$tablePrefix."blogposts2categories where blogposts_id = $id and ( blogcategories_id in (".implode(",",$ids)."))";
            $res = $GLOBALS['POOL']->dbwrite->query($query);
            $oldids = $res->fetchCol();
        } else {
            $query = "delete from ".$tablePrefix."blogposts2categories where blogposts_id = $id ";
            $res = $GLOBALS['POOL']->dbwrite->query($query);
            
            $oldids = array();
        }
        
        
        // add new categories
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
	        $seqid = $GLOBALS['POOL']->dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");

                $query = "insert into ".$tablePrefix."blogposts2categories (id, blogposts_id, blogcategories_id) VALUES ($seqid, $id, $value)";
                $res = $GLOBALS['POOL']->dbwrite->query($query);
            }
        }
    }
    function getElement($element,$clean = false) {
        $res = $this->xp->query("/atom:entry/atom:$element/*|/atom:entry/atom:$element/text()");
        $xml = "";
        foreach($res as $node) {
            if ($node->nodeType == 1 && $node->getAttribute("keep") == "true") {
                return false;
            }
            $xml .= $this->dom->saveXML($node);
        }
        
        if ($clean && $GLOBALS['POOL']->config->blogXssCleanPosts == 'true') {
            // clean up comment
            if (class_exists('tidy')) {
                $tidy = new tidy();
                if(!$tidy) {
                    throw new Exception("Something went wrong with tidy initialisation. Maybe you didn't enable ext/tidy in your PHP installation. Either install it or remove the tidy transformer from your sitemap.xml");
                }
            } else {
                $tidy = false;
            }
            
            if ($tidy) {
                $tidy->parseString($xml,$this->tidyOptions,"utf8");
                $tidy->cleanRepair();
                $xml = popoon_classes_externalinput::basicClean((string) $tidy);
                // and tidy it again 
                $tidy->parseString($xml);
                $tidy->cleanRepair();
                $xml = (string) $tidy;
            } 
        }
        return $xml;
        
        
    }
    
    
    function returnCategories() {
        $res = $GLOBALS['POOL']->db->query("select id, fullname from ".$this->tablePrefix."blogcategories where status = 1 order by fullname ");
        $xml = '<categories xmlns="http://sixapart.com/atom/category#"  xmlns:dc="http://purl.org/dc/elements/1.1/">';

        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .='<dc:subject xml:id="cat'.$row['id'].'">'.$row['fullname'].'</dc:subject>';   
        }
        
        $xml .= '</categories>';
        return $xml;
    }
    
    function contentOnWrite($content) {
    }
    
    static function getIdByUri ($uri,$path) {
        $tablePrefix = self::getTablePrefix($path);
        $res = $GLOBALS['POOL']->db->query("select id from ".$tablePrefix."blogposts where post_uri = '".$uri."'");
        return $res->fetchOne(0);
    }

    static function getUriById ($id) {
          $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $res = $GLOBALS['POOL']->db->query("select post_uri from ".$tablePrefix."blogposts where id = '".$id."'");
        return $res->fetchOne(0);
    }

    
    static function getUniqueUri($uri, $path) {
        $tablePrefix = self::getTablePrefix($path);
        //check if uri already exists
        if (trim($uri) == '') {
            $uri = 'none';
        }
        $query = "select id from ".$tablePrefix."blogposts where post_uri = '$uri'";
        
        $resid = $GLOBALS['POOL']->db->query($query);
        $newuri = $uri;
        $z = 1;
        while ($resid->numRows() > 0) {
            
            $z++;
            $newuri = $uri . "-". $z;
            $query = "select id from ".$tablePrefix."blogposts where post_uri = '$newuri'";
            $resid = $GLOBALS['POOL']->db->query($query);
        }
        $uri = $newuri;
 
        return $uri;
        
    }
    
    static function getTablePrefix($path) {
        if (!isset($tablePrefixes['$path'])) {
        
           $parts =  bx_collections::getCollectionAndFileParts($path, "output");
           $p = $parts['coll']->getFirstPluginMapByRequest("index","html");
           $p = $p['plugin'];
           $tablePrefixes['$path'] = $GLOBALS['POOL']->config->getTablePrefix().$p->getParameter($parts['coll']->uri,"tableprefix");
        }
        return $tablePrefixes['$path'];
    }
    
    public function sendTrackbacks($trackbacks,$title,$content,$uri) {
	    if ($trackbacks) {
            foreach(split(" ",$trackbacks) as $trackback) {
                $req = new HTTP_Request($trackback);
                $req->setMethod(HTTP_REQUEST_METHOD_POST);
                $req->addPostData("title", strip_tags($title));
                $req->addPostData("excerpt", substr(strip_tags($content),0,200)." ...");
                $uri = BX_WEBROOT_W.dirname($this->path).'/archive/'.$uri.'.html';
                $req->addPostData("url", $uri);
                if ($GLOBALS['POOL']->config->blogname) {
                    $blogname = $GLOBALS['POOL']->config->blogname;
                    $req->addPostData("blog_name", $blogname);
                } else {
                    $blogname = $GLOBALS['POOL']->config->sitename;
                    $req->addPostData("blog_name", $blogname);
                }
                
                if (PEAR::isError($req->sendRequest())) {
                    error_log("Trackback to $trackback didn't work.");
                }
            }
        }
    }
    
    public function getRemotePage($title,$content,$uri){
        $dom = new DomDocument;
        $dom->loadHTML($content);
        
        $domxpath= new DomXPath($dom);
        
        $results=$domxpath->query("//a");
        foreach($results as $result)
        {
            
            $href = $result->getAttribute("href");
            $sc = popoon_helpers_simplecache::getInstance();
            $page = $sc->simpleCacheHttpRead($href,1);
            if (preg_match('#trackback:ping="([^"]+)"#',$page,$matches)) {
                $this->sendTrackbacks($matches[1],$title,$content,$uri);     
            } 
        }
    }
}

class bx_streams_blog_post {
       public $title = null;
       public $content = null;
       /*public $content_extended = null;
       public $summary = null;*/
       public $uri = null;
       public $tags = null;
       public $trackbacks = null;
       public $autodiscovery = null;
       public $status = null;
       public $date = null;
       public $id = null;
       public $info = null;
       
          
       
       public function getInfo() {
           if (!$this->info) {
                 $this->info = new domdocument();
                 $this->info->appendChild($this->info->createElement("info"));
           }
           return $this->info;
       }
       public function appendInfoString($infoString) {
           if ($infoString) {
               $info = $this->getInfo();
               $dom = new domdocument();
               $dom->loadXML($infoString);
               $info->documentElement->appendChild($info->importNode($dom->documentElement,true));
           }
       }
       
       public function getInfoString() {
           if (!$this->info) {
               return "";
           }
           if (!$this->info->documentElement->hasChildNodes()) {
               return "";
           }
           $xml = "";
           foreach($this->info->documentElement->childNodes as $child) {
               $xml .= $this->info->saveXML($child);
           }
           return $xml;
            
       }
}

