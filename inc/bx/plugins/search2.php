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
*/

class bx_plugins_search2 extends bx_plugin implements bxIplugin {
    
    /**
    * a static var to to save the instances of this plugin
    */
    static public $instance = array();
    protected $res = array();
    
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_search2($mode);
        } 
        return self::$instance[$mode];
    }
    
    /** 
    * You are not allowed to call the constructor from outside, therefore
    *  it's protected. You have to use getInstance()
    */
    protected function __construct($mode) {
    
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
       
       return $name.'.'.$ext.'.search2';
       
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
        
        $dom = new domdocument();
        $root = $dom->appendChild($dom->createElement("search2"));
        if (!empty($_GET['q'])) {
        
            $pages = $this->getPages($_GET['q']);
        
        
        foreach($pages as $key => $results) {
            $res = $root->appendChild($dom->createElement("results"));
            $res->setAttribute("type",$key);
            $c = 1;
            foreach($results['entries'] as $id => $v) {
                $e = $res->appendChild($dom->createElement("entry"));
                $e->appendChild($dom->createElement("count",$c++));
                $e->appendChild($dom->createElement("url",$v['url']));
                $e->appendChild($dom->createElement("title",$v['title']));
                $e->appendChild($dom->createElement("text",$v['text']));
                $e->appendChild($dom->createElement("mod",$v['lastModified']));
                $e->appendChild($dom->createElement("id",$v['id']));
            }
        }
        }
        
        return $dom;
    }
    
    protected function getPages($search) {
        $pages =  array();
        $options = array ('searchStart' => 0 , 'searchNumber' => 2);
        $p['fulltext'] = $this->getFulltextPages($search,$options);
        return $p;
    }
    
    protected function getFulltextPages($search,$options) {
        /*if (!empty($options['pathRestrictions'])) {
            $pathRestriction = $options['pathRestrictions'];
        } else {
            $pathRestriction = null;
        }
        $excludePath = $options['excludePath'];
        */
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        $query = "select  properties.path, properties.value, sum(MATCH (value) AGAINST (". $db->quote($search) .")) as cnt
        from ".$tablePrefix."properties as properties  where 
        MATCH (value) AGAINST (" . $db->quote($search) ." ) ";
        /*if ($excludePath) {
            $query .= " and properties.path != ".$db->quote($excludePath) ." ";
        }
        if ($pathRestriction) {
            $query .= " and (properties.path like ".$db->quote($pathRestriction) .") ";
        }*/
        $query .= "group by properties.path order by cnt DESC LIMIT ".$options['searchStart'].",".$options['searchNumber'];
        $res = $db->query($query);
        $ids = array();
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $id = $row['path'];
            $parts = bx_collections::getCollectionAndFileParts($id);
            $reso = $parts['coll']->getPluginResourceById($parts['rawname']);
            //$parts['coll']->getParentCollection()
            if ($reso) {
                $lang = $reso->getLanguage();
                $displayName = $parts['coll']->getDisplayName($lang);
                if ($displayName == $parts['coll']->uri) {
                    $displayName = "";
                }
                $parent = $parts['coll']->getParentCollection();
                while ($parent) {
                    $_d = $parent->getDisplayName($lang);
                    if ($_d != $parent->uri) {
                        $displayName = $_d . " :: " . $displayName;
                    }
                    $parent = $parent->getParentCollection();                
                }
                $title = $reso->getTitle();
                if ($title != "index") { 
                    $displayName = $displayName . " :: " . $title;
                    
                }
                $ids[$id]['title'] =  $displayName;
                $ids[$id]['url'] = str_replace("/index.html","/",$reso->getOutputUri());
                //$relatedIds[$id]['resourceDescription'] = $res->getResourceDescription();
                $ids[$id]['lastModified'] = $reso->getLastModified();
                $ids[$id]['text'] = bx_helpers_string::truncate($row['value']);
                $ids[$id]['id'] = $id;
                //$relatedIds[$id]['status'] = $res->getStatus();
            }
            
           // $ids = $res->fetchAll(MDB2_FETCHMODE_ASSOC,"path",true);
        }
        
        return array('entries' => $ids);
    }
    
}