<?php 
/*
* Yahoo Search-Plugin
*
* BX_INC_DIR.'bx/plugins/yahoosearch.php'
*/ 
 
/**
* This plugin does not really do anything. It's just a simple example 
* of how a plugin is workting.
*
* calling it on e.g. /hello will output "Hello World"
* calling it on e.g. /hello/foo will output "Hello foo"
*
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
	    if($testSite != ""){
		   $dom =   bx_helpers_simplecache::staticHttpReadAsDom("http://boss.yahooapis.com/ysearch/web/v1/".$query."+site:".$testSite."?appid=".$bossAppId."&start=".$dirname."&format=xml");
	    }else{
		    $dom =   bx_helpers_simplecache::staticHttpReadAsDom("http://boss.yahooapis.com/ysearch/web/v1/".$query."+site:".$_SERVER['HTTP_HOST']."?appid=".$bossAppId."&start=".$dirname."&format=xml");
	    }
	    return $dom; 
    } 
 
 
    public function isRealResource($path , $id) { 
 
        return true; 
    } 
 
} 
?> 
