<?php
//header("Content-type: text/xml");
class bx_plugins_aggregator extends bx_plugin implements bxIplugin{
    
    static public $instance = array();
    
    
    /**
    The table names
    */
    
    protected $db = null;
    protected $tablePrefix = null;
    
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_aggregator($mode);
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
    
    public function getContentById($path,$id) {
        
        define('MAGPIE_CACHE_DIR',BX_TEMP_DIR.'magpie/');
        
        include_once('magpie/rss_fetch.inc');
        
        $query = "select rss_link from ".$GLOBALS['POOL']->config->getTablePrefix()."bloglinks where rss_link != ''";
        $res = $GLOBALS['POOL']->db->query($query);
        $feeduris = array();
        $i = 0;
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $feeduris[$i] = $row['rss_link'];
            $i++;
        }
        $allentries = array();
        foreach($feeduris as $feeduri){
            $rss = fetch_rss($feeduri);
        
            foreach($rss->items as $feed)
            {           
                $feed['date'] = $this->getDcDate($feed);
                $feed['dateiso'] = gmdate("Y-m-d\TH:i:s\Z",strtotime($feed['date']));
                $feed['name'] = $rss->channel['title'];
                $allentries[] = $feed;
            }
        }
        usort($allentries, array($this,"sortByDate"));
        $total = count($allentries);
        $startEntry  = isset($_GET['start']) ? $_GET['start'] : 0;
        
        $feeds = array_slice($allentries, $startEntry, 10);
        $xml = '<feeds>';
        foreach($feeds as $feed) {
            $xml .= "<feed>";
            $xml .= "<title>".$feed['title']."</title>";
            $xml .= "<date>".$feed['date']."</date>";
            $xml .= "<dateiso>".$feed['dateiso']."</dateiso>";
            $xml .= "<link>".$feed['link']."</link>";
            $xml .= "<name>".$feed['name']."</name>";
            
            if (isset($feed['content']) && isset($feed['content']['encoded'])) {
                $xml .= "<content>".htmlspecialchars($feed['content']['encoded'])."</content>";
            }
            $xml .= "</feed>";
        }
        
        
        $end =   (($startEntry + 10) > $total) ? $total : $startEntry + 10;
        $xml .= '<span class="blog_pager_prevnext">';
        
        if ($startEntry >= 10) {
            $xml .='<a href="'.$path.'?start='.($startEntry -10).'">Prev</a>';
        }
        if ($startEntry < ($total - 10)) {
            $xml .=' <a href="'.$path.'?start='.($startEntry + 10).'">Next</a>';
        }
        $xml .= '</span>';
        
        $xml .= '<span  class="blog_pager_counter">'.($startEntry + 1) .'-'. ($end) .'/'.$total.'</span>';
        
        
        $xml .= "</feeds>";
        $dom = new domDocument();
        $dom->loadXML($xml);   
        return $dom;
    }
    
    function sortByDate($a, $b){
        if ($a['date'] < $b['date']) {
            return 1;
        }
        return -1;
    }
    
    function getDcDate($item, $nowOffset = 0, $returnNull = false) {
        //we want the dates in UTC... Looks like MySQL can't handle timezones...
        //putenv("TZ=UTC");
        if (isset($item['dc']['date'])) {
            $dcdate = $this->fixdate($item['dc']['date']);
        } elseif (isset($item['pubdate'])) {
            $dcdate = $this->fixdate($item['pubdate']);
        } elseif (isset($item['issued'])) {
            $dcdate = $this->fixdate($item['issued']);
        } elseif (isset($item['created'])) {
            $dcdate = $this->fixdate($item['created']);
        } elseif (isset($item['modified'])) {
            $dcdate = $this->fixdate($item['modified']);
        } elseif ($returnNull) {
            return NULL;
        } else {
            //TODO: Find a better alternative here
            $dcdate = gmdate("Y-m-d H:i:s O",time() + $nowOffset);
        }
        return $dcdate;
        
    }
    
    function fixdate($date) {
        $date =  preg_replace("/([0-9])T([0-9])/","$1 $2",$date);
        $date =  preg_replace("/([\+\-][0-9]{2}):([0-9]{2})/","$1$2",$date);
        $date =  gmdate("Y-m-d H:i:s O",strtotime($date));
        return $date;
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
           // $res->props['title'] = $this->db->queryOne("select text from ".$this->tablePrefix.$this->linksTable." where id = ".$id); 
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
        return array();
    }
    
    /***
        Internal Methods, only needed by that class
    ***/
    
    /**
    * Returns all links as XML
    */
    
}
?>
