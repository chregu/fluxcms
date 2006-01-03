<?php

// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+

/** 
* This plugin shows a list of links from the bloglinks table
* It shows them in categories
* It will also use some metaindex features for tags and searching
*
* To use this plugin in a collection, put the following into .configxml
*** 
    <bxcms xmlns="http://bitflux.org/config">
        <plugins>
            <parameter name="xslt" type="pipeline" value="links.xsl"/>
            <extension type="html"/>
            <plugin type="links" >
            <!-- for del.icio.us style output (no cats, sort by date) -->
            <parameter name="style" value="delicious"/>
            </plugin>
            <plugin type="navitree"></plugin>
        </plugins>
    </bxcms>
***
* See also the links.xsl in your themes folder for the actual output
*/

class bx_plugins_links extends bx_plugin implements bxIplugin {
    
    /**
    * a static var to to save the instances of this plugin
    */
    static public $instance = array();
    protected $res = array();
    
    /**
    The table names
    */
    public $linksTable = "bloglinks";
    public $categoryTable = "bloglinkscategories";
    
    protected $db = null;
    protected $tablePrefix = null;
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_links($mode);
        } 
        return self::$instance[$mode];
    }
    
    /** 
    * You are not allowed to call the constructor from outside, therefore
    *  it's protected. You have to use getInstance()
    */
    protected function __construct($mode) {
         // Get the global table prefix
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        // get the db object
        $this->db = $GLOBALS['POOL']->db;
         $this->mode = $mode;
    }
    
    /*** 
        Action methods. 
        This are called from the bxcms popoon action 
     ***/
    
    /**
    * This function is called by the action to check, if it's a "RealResource"
    *  meaning that it actually has something to display
    * If all plugins in a collection return false, a page not found exception is 
    *  thrown
    * For this plugin, we just assume, it has always "something to say"
    *
    * @param    string  $path   The collectionURI
    * @param    string  $id     The id of the request, 
    *                           returned by getIdByRequest                          
    * @return   bool            If it is a RealResource or not.
    * @see      getIdByRequest 
    */
    public function isRealResource($path , $id) {
        return true;
    }
    
    /**
    * Every plugin has to return a unique id for a request.
    * If we for example are in the collection /links/
    *  and the request is /links/foobar.html, we get
    *  $path = /links/, $name="foobar", $ext="html"
    * If the request is /links/something/foobar.html
    *  and there is no collection "something", then name
    *  is "something/foobar"
    * Usually you should not be too concerned about the extension
    *  since that can be differently, if you do match on different
    *  extensions in .configxml
    *
    * In this example, we just return the filename part and add
    *  .links to it, to make it unique
    *
    * @param    string  $path   The collectionURI
    * @param    string  $name   The filename part of the request
    * @param    string  $ext    The extension part of the request
    * @return   string          A unique id
    */
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
       
       return $name.'.'.$this->name;
       
    } 
    
    /***
        Content methods
        The actual content getting methods
     ***/
    
    /**
    * The actual "pulling the content" method
    * This gets called in the bxcms generator popoon component
    * It has to return a DomDocument with the content
    *
    * @param    string  $path   The collectionURI
    * @param    string  $id     The id of the request, 
    *                           returned by getIdByRequest                          
    * @return   DomDocument     A DomDocument with the content
    * @see      getIdByRequest 
    */
    
    public function getContentById($path, $id) {
        
        // get the dirname and check, if we have one
        // this helps finding links like category/3.html
        $dirname = dirname($id);
        
        switch ($dirname) {
            case "category":
                //get the cat id out of $id
                $catid = preg_replace("#category/([0-9]+)\.links#","$1",$id);
                // and put a where restriction for getLinks
                return $this->getLinks($path," categories.id  = " .(int) $catid);
                break;
            case "tag":
                //get the tag name out of $id
                $tagname = preg_replace("#tag/(.+)\.links#","$1",$id);
                //get all ids with that tag within this path
                $taglinks = bx_metaindex::getRelatedIdsByTags(array($tagname),null,$path.'%');
                // and now create a "funny" query for getting only the links with that tag.
                $where = "concat('$path',links.id,'.links') in ('".implode("','",array_keys($taglinks))."')";
                return $this->getLinks($path,$where);
                break;
            default:
                // show all links if id = index.links
                if ($id == "index.links") {
                    return $this->getLinks($path);
                } else {
                    //otherwise show only a single link
                    // casting to (int) will get rid of .links
                    return $this->getLinks($path, " links.id = ". (int) $id);
                }
        }
        
        
    }
    
    /***
       UNTIL HERE IS ALL WHAT IT NEEDS FOR A BASIC IMPLEMENTATION
       (to just output a page with all links)
        What follows is additional juice.
     ***/
    
    
    public function getResourceById($path, $id, $mock = false) {
        $pathid = $path.$id;
        if (!isset($this->res[$pathid])) {
            $res = new bx_resources_simple($pathid);
            $id = (int) $id;
            $res->props['title'] = $this->db->queryOne("select text from ".$this->tablePrefix.$this->linksTable." where id = ".$id); 
            $res->props['outputUri'] = $path.$id.".html"; 
            $res->props['resourceDescription'] = "Link";
            $this->res[$pathid] = $res;
        }
        return $this->res[$pathid];
    }
    
    /***
       admin methods
     ***/  
     
    /**
    * to actually being able to edit links in the admin, we have to return
    *  true here, if the admin actions asks us for that.
    * We don't care about path,id, etc here
    */
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    /**
    * we need to "register" what editors are beeing able to handle this plugin
    */
    
    public function getEditorsById($path, $id) {
        return array("links");
    }
    
    /***
        Internal Methods, only needed by that class
    ***/
    
    /**
    * Returns all links as XML
    */
    
    protected function getLinks($path, $where = null) {
       
        
        /* for the sake of simplicity, we use XML_db2xml,
            which makes it quite easy to get an XML out of a DB query
            even, if it's not that nice to look at
            */
        $db2xml = new XML_db2xml($this->db,"links");
         $style = $this->getParameter($path,"style");
         
        if ($style == "delicious") {
         $query = "select links.link, links.text, links.id, links.description, links.date from ".$this->tablePrefix.$this->linksTable." as links ";
                    
        } else {
        
        $query = "select categories.id, categories.name, links.id, links.text, links.link, links.description, links.date from ".$this->tablePrefix.$this->categoryTable." as categories
                    left join ".$this->tablePrefix.$this->linksTable." as links
                    on links.bloglinkscategories  = 
                       categories.id";
        }
        if ($where) {
            $query .= " where $where ";
        }
        
       
        if ($style == "delicious") {
              $query .= " order by links.date DESC";
              $idxpath = "/links/result/row/id";
        } else {
            $query .= " order by categories.rang, links.rang";
            $idxpath = "/links/result/row/row/id";
        }
        $res = $this->db->query($query);
        
        //get ids from the 3rd col for the later tags retrieval
        $_ids = $res->fetchCol(2);
        
        //seek back to the start of the result set
        $res->seek(0);
        //and make a domdocument out of that resultset
        $dom = $db2xml->getXMLObject($res);

        $linksids = array();
        //create the path to the $ids
        foreach ($_ids as $id) {
            $linksids[] = $path.$id.".links";
        }
        // get tags from metaindex with the found linkids from above
        $tags = bx_metaindex::getTagsByIds($linksids);
        
        //create a xpath object
        $xp = new domxpath($dom);
        //loop through all link ids
        foreach ($xp->query($idxpath) as $link) {
            $id = $link->nodeValue;
            // if we have tags, create elements for each one and append them to the link node
            $linkid = $path.$id.'.links';
            if (isset($tags[$linkid])) {
                foreach($tags[$linkid] as $tag) {
                    $tagnode = $dom->createElement("tag",$tag);
                    $link->parentNode->appendChild($tagnode);
                }
                //get related links
                $related = bx_metaindex::getRelatedInfoByTags($tags[$linkid],$linkid);
                foreach ($related as $id => $value) {
                    $tagnode = $dom->createElement("related",$value['title']);
                    $tagnode->setAttribute("href",$value['outputUri']);
                    $tagnode->setAttribute("title",$value['resourceDescription']);
                    $link->parentNode->appendChild($tagnode);
                }
            }
        }
        //add the style to the document element for using it in the xslt
        $dom->documentElement->setAttribute("style",$style);
        return $dom;           
    }
}