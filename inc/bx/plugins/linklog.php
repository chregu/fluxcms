<?php
/**
 * bx_plugins_linklog
 * 
 * Enables to keep links in place comparable to del.icio.us
 * 
 * @author Alain Petignat
 * @todo Pager
 * @todo display related issues from del.icio.us
 * 
 * */
/*
* BX_INC_DIR.'bx/plugins/linklog.php'
*/
/**
* To use this plugin in a collection, put the following into .configxml
* and call /admin/webinc/install/linklog/ to create the databases.
*** 
<bxcms xmlns="http://bitflux.org/config">
    <plugins inGetChildren="false">
        <extension type="xml"/>
        <file preg="#plugin=#"/>
        <plugin type="linklog">
        </plugin>
    </plugins>

    <plugins>
        <parameter name="xslt" type="pipeline" value="linklog.xsl"/>
         <extension type="html"/>
         <plugin type="linklog">
         </plugin>
         <plugin type="navitree"></plugin>
    </plugins>

    <plugins inGetChildren="false">
        <extension type="xml"/>
        <file preg="#rss$#"/>
        <parameter name="output-mimetype" type="pipeline" value="text/xml"/>
        <parameter type="pipeline" name="xslt" value="../standard/plugins/linklog/linklog2rss.xsl"/>
        <plugin type="linklog">
            <parameter name="mode" value="rss"/>
        </plugin>
    </plugins>
</bxcms>
*
* See also the linklog.xsl for the output
*/
class bx_plugins_linklog extends bx_plugin implements bxIplugin {
    /*
    * The table names
    */
    private $linksTable 	    = "linklog_links";
    private $tagsTable		    = "linklog_tags";
    private $links2tagsTable 	= "linklog_links2tags";   
    
    /*
     * database
     */
    private $db = null;
    private $tablePrefix = NULL;
    private $cache4tags;
    private $isLoggedIn = false;

    // Variable for the CMS:    
    static public $instance = array();

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
    
    // this gets called on every instance of the class 
    protected function __construct($mode) {
        $this->tablePrefix 		= $GLOBALS['POOL']->config->getTablePrefix();
        $this->db 		 	= $GLOBALS['POOL']->db;
        $this->mode 			= $mode;
		
        $this->cache4tags	= BX_TEMP_DIR."/". $this->tablePrefix."linklog_tags.cache";
        
        // check if logged in:
        $perm = bx_permm::getInstance();
        if($perm->isLoggedIn()){
            $this->isLoggedIn = true;
        }
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
        // when a plugin is called:
        if (strpos($id,"plugin=") === 0) {
            return $this->callInternalPlugin($id, $path);
        }

        /**
         * reserved values:
         * 
         * .           (newest links, configurable output)
         * 
         * all        (all links, mainly for testing)
         * 
         *  */
		if(strpos($dirname, 'archive') === 0){
        	return $this->getArchive($dirname);
        }elseif(strpos($dirname, '_fetch') === 0){
        	return $this->fetchDeliciousFeeds();
        }elseif(strpos($dirname, '.') === 0){
        	return $this->getSplash();
        }else{
        	return $this->getLinksByTag($id);
        }

    } 
       
    /**
     * getSplash
     * 
     * By default returning newest links
     * */
    private function getSplash(){

        $sql = 'SELECT
                  links.*,' .
                  'DATE_FORMAT(links.time, ' .'"%Y-%m-%dT%H:%i:%SZ") as isotime '.
                  'FROM '.$this->tablePrefix.$this->linksTable.' links ' .
                  'ORDER BY links.time desc limit 0,30';             

		$res = $this->db->query($sql);
		
        if (MDB2::isError($res)) {
        	throw new PopoonDBException($res);
		}
                  
        return $this->processLinks($res);
    }      
    
    /**
    * getArchive
    * 
    * @param string like 2007-06-05 or 2007-06 or 2007
    * @return preprocessed linklist
    */
    private function getArchive($path){
        
        $where = str_replace('archive/', '', mysql_escape_string($path));
        
        $sql = 'SELECT DISTINCT links.*, ' .
                'DATE_FORMAT(links.time, "%Y-%m-%dT%H:%i:%SZ") as isotime '.
                  'FROM '.$this->tablePrefix.$this->linksTable.' links '.
                  'RIGHT JOIN '.$this->tablePrefix.$this->links2tagsTable.' map ' .
                  'ON links.id=map.linkid
                   LEFT JOIN '.$this->tablePrefix.$this->tagsTable.' tags ON ' .
                  'map.tagid=tags.id WHERE links.time LIKE "'.$where.'%" ' .
                  'ORDER BY links.time DESC';
                  
                $res = $this->db->query($sql);
                if (MDB2::isError($res)) {
                    throw new PopoonDBException($res);
                }
                
                return $this->processLinks($res, $meta);        
        
        
    }
    
    

    /*
    *
    * fetch the <deliciousName>-RSS passed via .configxml and locally inserts
    * links from del.iciou.us/<deliciousName>
    *
    * FIXME: function way too long
    */
    private function fetchDeliciousFeeds(){

        $deliciousName = $this->getParameter($this->path, 'deliciousname');
        if($deliciousName == "" || !$deliciousName){
            return;
        }
        
        define('MAGPIE_CACHE_DIR',BX_TEMP_DIR.'magpie/');
        include_once('magpie/rss_fetch.inc');

        // @todo pass them via configxml, for now, i am only testing :)
//        $name = ;
        $feeduris[] = $myuri = 'http://del.icio.us/rss/'. $deliciousName;

        /*
        * Add more feeds like this:
        *
        * $feeduris[] = 'http://del.icio.us/rss/foo';
        * $feeduris[] = 'http://del.icio.us/rss/bar';
        */
        $links = array();

        foreach($feeduris as $feeduri){
            
            $rss = fetch_rss($feeduri);
            
            foreach($rss->items as $feed)
            {           
                /*                 */
                $feed['date']    = bx_plugins_aggregator::getDcDate($feed);
                $feed['dateiso'] = gmdate("Y-m-d\TH:i:s\Z",strtotime($feed['date']));

                $feed['name']    = $rss->channel['title'];
                $links[]    = $feed;
            }
        }

        usort($links,  array(bx_plugins_aggregator,"sortByDate"));

        $editor = new bx_editors_linklog();

        $mycleaneduri = $this->simpleCleanUri($myuri);

        foreach($links as $link){

            $data = array(
                'title' => $link['title'],
                'url'   => $link['link'],
                'description' => $link['description'],        
                'tags' => $link['dc']['subject'],
                'time' => $link['date'],
            );
           if( ! strpos($link['name'], $deliciousName) ){
                $data['via'] .= '' . end( explode ("/", $this->simpleCleanUri($link['name']) ) ) . '';
            }

            $res = $editor->insertLink($data);

        }

	    @unlink($this->cache4tags);
	    $this->mapTags2Links();
        
    }
    
    private function simpleCleanUri($myuri){
        return str_replace(array('http://', 'rss'), array('',''), $myuri);
    }
    
    /**
    * @param string something like "music bla-music"/index.html.linklog
    * @return string "music bla-music"
    */
    private function getQuerystringFromId($id){
        if (($pos = strrpos($id,"/")) > 0) {
            return substr($id,0,$pos);
        }
    }
    /*
    * @param string e.g. "include+include2-exclude-exclude2"
    * @return array array('includes' => $includes, 'excludes' => $excludes);
    */
    private function splitQuerystringToParams($query){
        $includes = explode(" ", $query);
        $excludes = false;
        
        for($i = 0; $i < count($includes); $i++){
            
            if(strpos($includes[$i], '-')){
                
                $currentInclude = $includes[$i]; // save temporarly
                $includes[$i]   = substr($includes[$i], 0, strpos($includes[$i], '-')); // remove the --tags from +tag
                $currentExclude = str_replace($includes[$i] . '-', '', $currentInclude);
                
                foreach(explode("-", $currentExclude) as $tag){
                    $excludes[] = $tag;                    
                }

            }
        }

        return array('includes' => $includes, 'excludes' => $excludes);
    }
    
    /**
     * getLinksByTag
     * 
     * @param $id 
     * 
     */
    private function getLinksByTag($id){
        
        $querystring = $this->getQuerystringFromId($id);
        $vars        = $this->splitQuerystringToParams($querystring);
        
        $sql  = $this->getBasicLinkQuery();

        $sql .= $this->getWhereIncludesTags($vars['includes']);
        
        if($vars['excludes']){
            $sql .= $this->getWhereExcludesTags($vars['excludes']);        
        }
        
        $sql .= $this->getHavingCount($vars['includes']);
        $sql .= $this->getBasicLinkQueryOrderBy();
        
        $meta = $this->getMetaData($vars);        
      
//        print '<pre>' ;print_r(array($sql));         print '</pre>' ;

        $links = $this->db->query($sql);
                
		if (MDB2::isError($links)) {
        	throw new PopoonDBException($links);
		}
		
        return $this->processLinks($links, $meta);
        
    }       

    private function getHavingCount($includes){
		return ' GROUP BY links.id HAVING COUNT( linkid ) = ' . count($includes) . ' ';    	
    }
    
    /*
    * @param Array Tags to be included
    */
    private function getWhereIncludesTags($includes){
        return "\n" .'AND tags.fulluri '. "\n" .'IN ("'.implode('", "', $includes) .'")'. "\n" ;
    }
    
	/*
	*
	* */
    private function getWhereExcludesTags($excludes){
    	return  "\n" .'AND links.id NOT IN (SELECT links.id FROM '.$this->tablePrefix.$this->linksTable.' links, '.$this->tablePrefix.$this->links2tagsTable.' map, '.$this->tablePrefix.$this->tagsTable.' tags WHERE links.id = map.linkid AND map.tagid = tags.id AND tags.fulluri  in  ("'.implode('", "', $excludes) .'"))';
    }

    private function getBasicLinkQueryOrderBy(){
        return "\n" . 'ORDER BY links.time DESC';
    }
    
    /*
	* @param array $vars(excludes => array(), 'exludes' => false || array)
	* */
    private function getMetaData($vars){
	
    	
        return; 
        $q = "select * from ".$this->tablePrefix.$this->tagsTable." " .
             "where ".$this->tablePrefix.$this->tagsTable.".fulluri = '$cat' "; 
        $res = $GLOBALS['POOL']->db->query($q);
        
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        $c = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

        $meta = "<meta><title>".$c['name']."</title></meta>";        

    }


    private function getBasicLinkQuery(){
        $sql = 'SELECT links.*,  ' . "\n" .
              'DATE_FORMAT(links.time, ' . '"%Y-%m-%dT%H:%i:%SZ") as isotime '. "\n" .
              'FROM '.$this->tablePrefix.$this->linksTable . ' links,  '.
              $this->tablePrefix.$this->links2tagsTable . ' map,  '.
              $this->tablePrefix.$this->tagsTable . ' tags '. "\n" .
              'WHERE links.id=map.linkid AND map.tagid = tags.id ';
        return $sql;
        
        
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
       //print '<pre>';	var_dump($row);print '</pre>';
            $xml .= "<link>";
            $xml .= "<id>".$row['id']."</id>";
            $xml .= "<title>".$row['title']."</title>";
            $xml .= "<description>".$row['description'] ."</description>";

            $xml .= '<archive>'.$this->getArchiveLinkFromTime($row['time']).'</archive>';
            $xml .= "<time>".$row['time']."</time>";
            
            $xml .= "<isotime>".$row['isotime']."</isotime>";           

			$xml .= "<url>".$row['url']."</url>";
            if($this->isLoggedIn){
                    $xml .= "<edituri>".BX_WEBROOT_W."/admin/edit".$this->path."edit/".$row['id']."</edituri>";   
                    $xml .= "<deleteuri>".BX_WEBROOT_W."/admin/edit".$this->path."delete/".$row['id']."</deleteuri>";                    
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
    
    
    /*
     * this is a really timeconsuming method, since it maps all links to its specific tags
     * 
     * @return array $map 
     * 
     * array(
     *     $linkid => array(
     *                     $catid1 => ...
     *                     $catid2 => ...
     *                     ...
     *                 )
     *  ...
     * )
     */
    private function mapTags2Links(){
    	
            if(file_exists($this->cache4tags)){
                return unserialize(file_get_contents($this->cache4tags));
            }
            
            
			$query = "SELECT * FROM ".$this->tablePrefix.$this->tagsTable . ' ORDER BY name asc'; 
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
    
            $query = 'SELECT map.id, map.linkid, map.tagid FROM '. $this->tablePrefix.$this->links2tagsTable . ' map left join '.$this->tablePrefix.$this->tagsTable.' tags on map.tagid=tags.id order by tags.fulluri';

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
                if(!array_key_exists($row['linkid'], $map)){
                    $map[$row['linkid']] = array($tags[$row['tagid']]);
                }else{
                    array_push($map[$row['linkid']], $tags[$row['tagid']]);
                }
            }            
            
            // caching it, must be deleted when new link is added...
            file_put_contents($this->cache4tags, serialize($map));
            
            return $map;

    }
    
    /* ** */
    private function getArchiveLinkFromTime($datetime){

        $date = substr($datetime, 0, 10);
        $base = $this->path . 'archive';
        $dateparts = explode('-', $date);
        
        $i = 1;
        foreach($dateparts as $time){
            if($i > 1){
                $links[$i]['href'] =  $links[($i-1)]['href'] . '-'.$time;
            }else{
                $links[1]['href'] = $base . '/'.$time;
            }
            
            $links[$i]['text'] =  $time;                      
            
            $full[] =   '<a href="'.$links[$i]['href'].'/" title="'.$time.'">' . $time .'</a>';                      
            
            $i++;
        }
        
        $full = array_reverse($full);

        $link = '<![CDATA[' . "\n" . implode('.', $full) . "\n" . ']]>';

       return $link;
    }
    
    /* from here on reserved bx_plugin_* functions */
    
    /*
     * 
     * */
    public function isRealResource($path , $id) {
        return true;
    }
    
    /*
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
}
?>
