<?php 
/*
* Yahoo Search-Plugin
*
* BX_INC_DIR.'bx/plugins/yahoosearch.php'
*/ 
 
/*

* To use this plugin in a collection, put the following into .configxml
*** 
    <bxcms xmlns="http://bitflux.org/config">
        <plugins>
            <parameter name="xslt" type="pipeline" value="yahoosearch.xsl"/>
            <extension type="html"/>
            <plugin type="yahoosearch">
            </plugin>
            <plugin type="navitree"></plugin>
        </plugins>
    </bxcms>
*
*/ 
 
class bx_plugins_yahoosearch extends bx_plugin implements bxIplugin { 
 
 
    static public $instance = array(); 
    protected $res = array(); 
 
 
    public static function getInstance($mode) { 
 
        if (!isset(self::$instance[$mode])) { 
            self::$instance[$mode] = new bx_plugins_yahoosearch($mode); 
        }  
        return self::$instance[$mode]; 
    } 
 
 
    protected function __construct($mode) { 
 
        $this->mode = $mode; 
    } 
  
 
    public function getContentById($path, $id) { 
	    $dirname = dirname($id);  
	    if($dirname =="."){$dirname=0;}
	    if($_GET['query'] ==""){$dom = new domDocument; return $dom;}
	    $query = str_replace(' ', '%20', $_GET['query']);
	     $this->path = $path;
	    $bossAppId = $this->getParameter($path, 'BOSSAppID');
	    $testSite = $this->getParameter($path, 'testSite');
	    
	    $yahoourl = "http://boss.yahooapis.com/ysearch/web/v1/".$query."+site:";
	    
	    if($testSite != ""){
		    $yahoourl .= $testSite;
	    }else{
		    $yahoourl .=  $_SERVER['HTTP_HOST'];
	    }
	    
	    $yahoourl .="?appid=".$bossAppId."&start=".$dirname;
	    
	    if($testSite != ""){
		$yahoourl .= "&type=".$_GET['type'];
	    }
	    
		$yahoourl .= "&format=xml";
	    
	     $dom =   bx_helpers_simplecache::staticHttpReadAsDom($yahoourl);
		     
	    return $dom; 
    } 
 
 
    public function isRealResource($path , $id) { 
 
        return true; 
    } 
 
}
