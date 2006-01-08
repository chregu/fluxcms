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
* To use this plugin in a collection, put the following into .configxml
*** 
<bxcms xmlns="http://bitflux.org/config">
<plugins>
<parameter name="xslt" type="pipeline" value="##pname##.xsl"/>
<extension type="html"/>
<plugin type="##pname##" >
</plugin>
<plugin type="navitree"></plugin>
</plugins>
</bxcms>
***
* See also the ##pname##.xsl in your themes folder for the actual output
*/

class bx_plugins_##pname## extends bx_plugin implements bxIplugin {
    
    /**
    * a static var to to save the instances of this plugin
    */
    static public $instance = array();
    protected $res = array();
    public $name = "##pname##";
    
    /**
    The table names
    */
    public $tablename = null;
    
    protected $db = null;
    protected $tablePrefix = null;
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_##pname##($mode);
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
        
        $this->tablename = $this->getParameter($path,"tablename");
        if ($this->tablename) {
            // show all links if id = index.links
            if ($id == "index.##pname##") {
                $xml =  $this->getData($path);
                
            } else {
                //otherwise show only a single link
                // casting to (int) will get rid of .links
                $xml = $this->getData($path, " id = ". (int) substr($id,2));
            }
        } else {
            $xml = $this->getEmptyPage($id);
        }
        
        $xml = '<div xmlns="http://www.w3.org/1999/xhtml"  xmlns:i18n="http://apache.org/cocoon/i18n/2.1">'.$xml.'</div>';
        
        return domdocument::loadXML($xml);
    }
    
    /***
    UNTIL HERE IS ALL WHAT IT NEEDS FOR A BASIC IMPLEMENTATION
    (to just output a page with all links)
    What follows is additional juice.
    ***/
    
    
    public function getResourceById($path, $id, $mock = false) {
        /*$pathid = $path.$id;
        if (!isset($this->res[$pathid])) {
            $res = new bx_resources_simple($pathid);
            $id = (int) $id;
            $res->props['title'] = $this->db->queryOne("select text from ".$this->tablePrefix.$this->linksTable." where id = ".$id); 
            $res->props['outputUri'] = $path.$id.".html"; 
            $res->props['resourceDescription'] = "Link";
            $this->res[$pathid] = $res;
        }
        return $this->res[$pathid];
        */
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
        // return array("##pname##");
        return null;
    }
    
    /***
    Internal Methods, only needed by that class
    ***/
    
    
    protected function getEmptyPage($id) {
        
        $xml = '<p>
        <i18n:text>Called ##pname## plugin with id: '.$id.'</i18n:text></p>';
        
        return $xml;
    }
    
    /**
    * Returns all links as XML
    */
    
    protected function getData($path, $where = null) {
        $query = "select * from ".$this->tablePrefix.$this->tablename;
        
        if ($where) {
            $query .= " where $where ";
        }
        
        
        $res = $this->db->query($query);
        
        $xml = "<table>";
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        $overview = ($res->numRows() > 1) ? true : false;
        
        if ($overview) {
            array_splice($row,4);
        }
        
        $xml .= "<tr>";
        foreach ($row as $key => $value) {
                $xml .="<th>".htmlspecialchars($key)."</th>";   
            }
        $xml .= "</tr>";
        
        do {
            $xml .="<tr>";
            if ($overview) {
                array_splice($row,4);
            }
            foreach ($row as $key => $value) {
                $xml .="<td>".$value."</td>";   
            }
            $xml .="</tr>";
        } while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC));
        
        $xml .= "</table>";
        return $xml;            
        
    }
    
  }