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

class bx_plugins_metasearch extends bx_plugin implements bxIplugin {
    
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
            self::$instance[$mode] = new bx_plugins_metasearch($mode);
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
       
       return $name.'.'.$ext.'.search';
       
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
            case "data":
                //get the cat id out of $id
                $catid = preg_replace("#data/(.+)\.search#","$1",$id);
                // and put a where restriction for getLinks
                $dom = new domdocument();
                $root = $dom->appendChild($dom->createElement("result"));
                //print $_GET['p'];
                
                $options = array();
                $entriesperpage = 2;
                
                if(!isset($_GET['p'])) {
                    $_GET['p'] = 0;
                }
                
                if(!isset($_GET['query'])) {
                    $_GET['query'] = 0;
                }
                
                $options = array(
                'excludePath' => null,
                'pathRestrictions' => null,
                'searchStart' => 0,
                'searchNumber' => 50
                );
                
                $Infosfromsearch = bx_metaindex::getRelatedInfoBySearch($_GET['query'], $options);
                $count = count($Infosfromsearch);
                bx_helpers_pager::setValues($count,$_GET['p'] , $entriesperpage,$id);
                $query = $dom->createElement("query",$_GET['query']);
                $root->appendChild($query);
                
                $Infosfromsearch = bx_metaindex::getRelatedInfoBySearch($_GET['query'], $options);
                $count = count($Infosfromsearch);
                $found = $dom->createElement("found",$count);
                $root->appendChild($found);
                
                $currpage = $dom->createElement("currpage", $_GET['p']);
                $root->appendChild($currpage);
                
                $maxpages = bx_helpers_pager::getNumberOfPages($id);
                
                if(!$_GET['p'] == $maxpages-1){
                    $nextpage = $dom->createElement("nextpage",bx_helpers_pager::getNextPageFixed($id));
                    $root->appendChild($nextpage);
                }
                
                if($_GET['p'] != 0){
                    $prevpage = $dom->createElement("prevpage",$prevPage = bx_helpers_pager::getPrevPageFixed($id));
                    $root->appendChild($prevpage);
                }
                
                $maxpage = $dom->createElement("maxpages",$maxpages);
                $root->appendChild($maxpage);
                
                $firstdoc = $dom->createElement("firstdoc",bx_helpers_pager::getFirstDoc($id));
                $root->appendChild($firstdoc);
                
                $lastdoc = $dom->createElement("lastdoc",bx_helpers_pager::getLastDoc($id));
                $root->appendChild($lastdoc);
                
                $options = array(
                'excludePath' => null,
                'pathRestrictions' => null,
                'searchStart' => $_GET['p']*$entriesperpage,
                'searchNumber' => $_GET['p']*$entriesperpage + $entriesperpage
                );
                
                foreach (bx_metaindex::getRelatedInfoBySearch($_GET['query'], $options) as $result) {
                    $res = $dom->createElement("resource");
                    $res->setAttribute('uri',$result['outputUri']);
                    $res->setAttribute('cnt',$result['cnt']);
                    $res->setAttribute('resourceDescription',$result['resourceDescription']);
                    $res->setAttribute('lastmodified',$result['lastModified']);
                    $title = $dom->createElement("title",$result['title']);
                    $res->appendChild($title);
                    $root->appendChild($res);
                }
                return $dom;
                //return $this->getResult($path," categories.id  = " .(int) $catid);
                break;
        }
    }
    
    /***
        Internal Methods, only needed by that class
    ***/
    
    /**
    * Returns all links as XML
    */
    
   
}