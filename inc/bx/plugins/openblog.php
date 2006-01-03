<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+


class bx_plugins_openblog extends bx_plugin implements bxIplugin {
    
    private static $instance = array();
    protected static $tableNames = array();
    protected static $childrenSections = array();
    protected static $tableInfo = array();
    private static $resources = array();
    
    /*** magic methods and functions ***/
    
    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_openblog($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->mode = $mode;
    }
    
    public function getIdByRequest ($path, $name = NULL, $ext = NULL) {
        return "$name";
    } 
    
    public function getContentById($path, $id) {
        $dom = new domDocument();
        bx_global::registerStream("blog");
        
        @session_start();
        if ($id !== "" && $id !== "index") {
            if (isset($_SESSION['newpost']) && $_SESSION['newpost'] == $id) {
                $p = $id;
            } else {
                header("Location: /post/");
            }
        } else {
            if (isset($_SESSION['newpost'])) {
                unset($_SESSION['newpost']);
            }
            
            $p = 'newpost';
        }
         
        $dom->load(sprintf("blog://%s.xml",$p));
        return $dom;
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function handlePublicPost($path, $id, $data) {
        if(!empty($data['uri'])) {
            bx_global::registerStream("blog");
            
            $fd = fopen("blog://".$id,"w");
            if (!isset($data['id']) || !($data['id'])) {
            	$data['uri'] =  bx_streams_blog::getUniqueUri($data['uri']);
	    }
            
            
            $allowedTags = array('<h1>','<h2>','<h3>','<span>','<sub>','<div>','<sup>','<b>','<i>','<a>','<ul>','<li>','<ol>','<pre>','<blockquote>','<br/>','<p>');
            $data['content'] = strip_tags($data['content'],implode("", $allowedTags));       

            if (isset($data['nl2br']) && $data['nl2br'] == 1) {
			//our own nl2br
			$data['content'] = preg_replace("#\r#","",$data['content']);
			$data['content'] = preg_replace("#([^>])[\n]{2,}#","$1<br/>\n<br/>\n",$data['content']);
		     $data['content'] = preg_replace("#([^>])\s*([\n])#","$1<br/>$2",$data['content']);
	    }
            $data['content'] = bx_helpers_string::tidyfy($data['content']);
            $data['content'] = popoon_classes_externalinput::basicClean($data['content']);
            $data['content'] = bx_helpers_string::tidyfy($data['content']);
            
            fwrite($fd, '<entry xmlns="http://purl.org/atom/ns#">');
            fwrite($fd, '<author>'.$data['author'].'</author>');
            fwrite($fd, '<title>'.$data['title'].'</title>');
            fwrite($fd, '<id>'.$data['id'].'</id>');
            fwrite($fd, '<uri>'.$data['uri'].'</uri>');
            fwrite($fd, '<created>'.$data['created'].'</created>');
            fwrite($fd, '<atom:content type="application/xhtml+xml" xmlns:atom="http://purl.org/atom/ns#" xmlns="http://www.w3.org/1999/xhtml">'.$data['content'].'</atom:content>');
            fwrite($fd, '<categories xmlns="http://sixapart.com/atom/category#"  xmlns:dc="http://purl.org/dc/elements/1.1/">');
            
            if (isset($data['categories'])) { 
                fwrite($fd, "<dc:subject>".$data['categories']."</dc:subject>");
                /*
                foreach ($data['categories'] as $value => $cat) {
                    fwrite ($fd, '<dc:subject>'.$value.'</dc:subject>');
                }
                */
            }
            
            fwrite ($fd, '</categories>');
            fwrite($fd, '</entry>');
            fclose($fd);
            if ("/".$data['uri'].".html" != $id) {
                @session_start();
                $_SESSION['newpost'] = $data['uri']; 
                header("Location: ".$data['uri'] .".html");
                exit;
            }
            
        } 
    } 
     
   
    /**
    * FIXME:
    * see getChildren for the new way...
    */
    public function getResourceById($path, $id, $mock = false) {
       if (! isset (self::$resources[$id])) {
            $file = $this->getParameter($path,'src');
            $res = new bx_resources_application_dbform($id);
            /*$tables = array_keys($this->getTableNames($file));	
            if(count($tables) > 0) {
	            $res->table = $tables[0];
        	    $info = $this->getTableInfo($tables[0]);
	            $res->webdavId = $info['webdavId'];
        	    $res->langField = $info['langField'];
	            $res->chooser = $info['chooser'];
	    }*/
            self::$resources[$id] = $res;
	
        }
        return self::$resources[$id] ;
    }

        
    /* needed for structure2xml code */    
    public function getAttrib($value) {
        return null;
    }
    

    
    
}
?>
