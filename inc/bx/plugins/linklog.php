<?php
/*
* BX_INC_DIR.'bx/plugins/linklog.php'
*/
/**
* To use this plugin in a collection, put the following into .configxml
*** 
    <bxcms xmlns="http://bitflux.org/config">
        <plugins>
            <parameter name="xslt" type="pipeline" value="linklog.xsl"/>
            <extension type="html"/>
            <plugin type="linklog">
            </plugin>
            <plugin type="navitree"></plugin>
        </plugins>
    </bxcms>
*
* See also the linklog.xsl for the actual output
*/
class bx_plugins_linklog extends bx_plugin implements bxIplugin {
    /*
    * The table names
    */
    public $linksTable 		= "linklog_links";
    public $tagsTable			= "linklog_tags";
    public $links2tagsTable 	= "linklog_links2tags";   
    
    /*
     * database
     */
    protected $db = null;
    private $tablePrefix = NULL;
    
    /*
     * 
     */
    static public $instance = array();
    
    protected $isLoggedIn = false;
    
    /*
     * Navitree:
     
    static protected $tree = null;
    */
    
    /**
     * getInstance
     * 
     * returns an instance of the plugin
     * 
     * @param mode not used until now.
     * 
     */
    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_linklog($mode);
        } 
        return self::$instance[$mode];
    }
    /*
     * plain constructor ;)
     */
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
		
		/*
		 * check if logged in:
	     */
            $perm = bx_permm::getInstance();
	        if($perm->isLoggedIn()){
	        		$this->isLoggedIn = true;
	        }
        
        // $this->debug(get_defined_constants());
    }

    /**
     * getContentById
     * 
     * @param string $path
     * @param string $id 
	 */
    public function getContentById($path, $id) {

        $dirname = dirname($id);
        
        $this->path=$path;

        /* debug:
        print $id; // splash: "index.html.linklog, else "dir/file.html.linklog"
        print $dirname; // splash: ".", else "directory"
		*/

        /*
         * call internal stuff
         * @todo: make own function
         */
        if (strpos($id,"plugin=") === 0) {
            return $this->callInternalPlugin($id, $path);
        }

        /**
         * reserved values:
         * 
         * .   		(newest links, configurable output)
         * 
         * all		(all links, mainly for testing)
         * 
         * archive/  (give a time-axis containing chronologicaly added links)
         * 
         * archive/yyyy/mm/ (show links of a certain month)
         * 
         * detail/ (show detail of a link)
         * 
         *  */
        switch ($dirname) {
            case "all":
                return $this->getAll();
            case "archive":
                return $this->getArchive($id);
            case "detail":
                return $this->getDetail($id);
            case ".":
                return $this->getSplash();
            default:
                return $this->getLinksFromCategory($id);
        }

    } // done getContentById (function is way too long)
   
    /* ========================================================== fetching methods */
    
    /**
     * getSplash
     * 
     * By default returning newest links
     * */
	private function getSplash(){
        // $db2xml = new XML_db2xml($this->db,"links");

            $query = 'SELECT
                  '.$this->tablePrefix.$this->linksTable.'.id,
                  '.$this->tablePrefix.$this->linksTable.'.title,
                  '.$this->tablePrefix.$this->linksTable.'.url,
                  '.$this->tablePrefix.$this->linksTable.'.description,
                  '.$this->tablePrefix.$this->linksTable.'.time, ' .
                  'DATE_FORMAT('.$this->tablePrefix.$this->linksTable.'.time, "%Y-%m-%dT%H:%i:%SZ") as isotime '.
				 'FROM '.$this->tablePrefix.$this->linksTable.'  order by time desc limit 0,30';       
        
        $res = $this->db->query($query);
        return $this->processLinks($res);
	}      
    
    /**
     * getAll
     * 
     * fetching all links, ordered chronologically
     */
    private function getAll(){
        $db2xml = new XML_db2xml($this->db,"links");
        $query = "select * from ".$this->tablePrefix.$this->linksTable." order by time desc";
        $res = $this->db->query($query);
        return $this->processLinks($res);
    }       
    
    /**
     * getLinksFromCategory
     * 
     * @param $id 
     * 
     */
    private function getLinksFromCategory($id){
        
        if (($pos = strrpos($id,"/")) > 0) {
            $cat = substr($id,0,$pos);
            $id = substr($id, $pos + 1);
        } else {
            $cat = "";
        }

        if (isset($cat) && $cat && $cat != '_all') {
                
                /**
                 * contains current category
                 */
                $q = "select * from ".$this->tablePrefix.$this->tagsTable." " .
                     "where ".$this->tablePrefix.$this->tagsTable.".fulluri = '$cat' "; 
                
                $res = $GLOBALS['POOL']->db->query($q);
                
                if (MDB2::isError($res)) {
                    throw new PopoonDBException($res);
                }
                
                $c = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
                
                $meta = "<meta><title>".$c['name']."</title></meta>";
                 
            if (isset($c)) {     
            $q = 'SELECT
                  '.$this->tablePrefix.$this->linksTable.'.id,
                  '.$this->tablePrefix.$this->linksTable.'.title,
                  '.$this->tablePrefix.$this->linksTable.'.url,
                  '.$this->tablePrefix.$this->linksTable.'.description,
                  '.$this->tablePrefix.$this->linksTable.'.time, ' .
                  'DATE_FORMAT('.$this->tablePrefix.$this->linksTable.'.time, "%Y-%m-%dT%H:%i:%SZ") as isotime '.
				 'FROM '.$this->tablePrefix.$this->linksTable.'
                   RIGHT JOIN '.$this->tablePrefix.$this->links2tagsTable.' ON '.$this->tablePrefix.$this->linksTable.'.id='.$this->tablePrefix.$this->links2tagsTable.'.linkid
                   LEFT JOIN '.$this->tablePrefix.$this->tagsTable.' ON '.$this->tablePrefix.$this->links2tagsTable.'.tagid='.$this->tablePrefix.$this->tagsTable.'.id
                   WHERE '.$this->tablePrefix.$this->links2tagsTable.'.tagid='.$c['id'].' ORDER BY '.$this->tablePrefix.$this->linksTable.'.time DESC';
               
                $links = $this->db->query($q);
                
                 if (MDB2::isError($links)) {
                    throw new PopoonDBException($links);
                }
                return $this->processLinks($links, $meta);
            } 
        }        
    }       

    /**
     * processLinks
     * 
     * @param $links db-object containing link-data
     * @param $meta xml-string with metadata being displayed in title
     * 
     * */
    private function processLinks($links, $meta = false){
       $map2tags = $this->mapTags2Links();
       if(is_string($meta)){
           $xml   = "<links>";
           $xml  .= $meta;
       }else{
           $xml = "<links>";       
       }

       while($row = $links->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $xml .= "<link>";
            $xml .= "<id>".$row['id']."</id>";
            $xml .= "<title>".$row['title']."</title>";
            $xml .= "<description>".$row['description'] ."</description>";
            $xml .= "<time>".$row['time']."</time>";
            
            $xml .= "<isotime>".$row['isotime']."</isotime>";           
            
            $xml .= "<url>".str_replace('&','&amp;',$row['url'])."</url>";

            if($this->isLoggedIn){
            		$xml .= "<edituri>".BX_WEBROOT_W."/admin/edit/linklog/edit/".$row['id']."</edituri>";   
            		$xml .= "<deleteuri>".BX_WEBROOT_W."/admin/edit/linklog/delete/".$row['id']."</deleteuri>";                    
            }

            $xml .= "<tags>";


            
            // have fun with categories:
            $tags = $map2tags[$row['id']];
            if(is_array($tags)){
	            foreach($tags as $t){
	                $xml .= "<tag>";
	                $xml .= "<id>".$t['id']."</id>";                
	                $xml .= "<fulluri>".BX_WEBROOT_W.$this->path.$t['fulluri']."</fulluri>";                
	                $xml .= "<name>".str_replace('&','&amp;',$t['name'])."</name>";    
	                
	
	                
	                            
	                $xml .= "</tag>";                
	            }    
            }                    
            $xml .= "</tags>";  
            $xml .= "</link>";
        }
        
        $xml .= "</links>";
    
        $dom = new DomDocument();

        
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
    
    
    
    /* ============================================================ extended stuff */        
    
    /**
     * calls static plugin from extending class in /inc/bx/plugins/linklog/*
     * 
     * currently, only tags exists
     */
    private function callInternalPlugin($id, $path){
           // print $id;
            $plugin = substr($id,7);

            if ($pos = strpos($plugin,"(")) {
                $pos2 = strpos($plugin,")");
                $params = substr($plugin,$pos+1, $pos2 - $pos - 1);
                $plugin = substr($plugin,0,$pos);
                $params = explode(",",$params);
            }  else {
                $params = array();
            }
            
            $plugin = "bx_plugins_linklog_".$plugin;
            
            $xml =  call_user_func(array($plugin,"getContentById"), $path, $id, $params,$this->tablePrefix);
            
            // $xml = bx_plugins_linklog_tags::getContentById($path, $id, $params,$this->tablePrefix);
            
            // var_dump($xml);
            
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
    
    
    /* ========================================================== additional stuff */
    
    /*
     * this is a really timeconsuming method
     * must cache or improve!
     */
    private function mapTags2Links(){
        $query = "SELECT * FROM ".$this->tablePrefix.$this->tagsTable;
        $res = $this->db->query($query);    
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        /*
         * loop through all tags to create an array with its id as index
         */
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            $tags[$row['id']] = $row;
        }

        $query = "SELECT * FROM ".$this->tablePrefix.$this->links2tagsTable."";
        $res = $this->db->query($query);    
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        /*
         * loop through all merges, to be able to fetch a category of the 
         * link in one catch
         */
        $map = array();
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
            if(!is_array($map[$row['linkid']])){
                $map[$row['linkid']] = array($tags[$row['tagid']]);
            }else{
                array_push($map[$row['linkid']], $tags[$row['tagid']]);
            }
        }            
        // $this->debug($map);
        return $map;
    }

      
    
    /**
     * ?
     * 
     * */
    public function isRealResource($path , $id) {
        return true;
    }
    
    /**
    * to actually being able to edit links in the admin, we have to return
    * true here, if the admin actions asks us for that. We don't care about
    * path,id, etc here
    */
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
    /**
    * we need to "register" what editors are beeing able to handle this plugin
    */    
    public function getEditorsById($path, $id) {
        return array("linklog");
    }    
        
    /**/
    protected function debug($ar){
        print "<pre>";
        print_r($ar);
        print "</pre>";
    }

}
?>