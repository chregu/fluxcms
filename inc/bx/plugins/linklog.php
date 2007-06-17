<?php
/**
 * bx_plugins_linklog
 *
 * Enables to keep links in place comparable to del.icio.us
 *
 * @author Alain Petignat
 * @todo Pager
 * @todo display related issues from del.icio.us
 * @todo: shorten class, its a bit long :)
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

	protected $id 			= null;
	protected $path 		= null;	
	
	protected $db 			= null;
	protected $totalItems 	= null;
	
	protected $pageTitle 	= '';
	
	protected $tablePrefix  = '';
	protected $itemsPerPage	= 20;
	
	protected $currentPage  = 1;
	protected $view			= 'splash';
	
	
	protected $isLoggedIn 	= false;
	static public $instance = array();
	

	protected $cache4tags	= '';
	
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
		
		$this->db 		 		= $GLOBALS['POOL']->db;
		$this->tablePrefix 		= $GLOBALS['POOL']->config->getTablePrefix();
		$this->mode 			= $mode; // ??
		$this->cache4tags		= BX_TEMP_DIR."/". $this->tablePrefix."linklog_tags.cache";
		$this->setLoginStatus();
		
	}

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


	/**
	 * getContentById
	 *
	 * @param string $path
	 * @param string $id
	 */
	public function getContentById($path, $id) {
		$this->path		   = $path;
		$this->id		   = $id;		
		
		$this->currentPage = $this->getCurrentPage($_GET);
		$this->setView();
		
		return $this->getView();		

	}
	
	/**
	 * Sets the view depending on the URL
	 * 
	 * @return void
	 * */
	private function setView(){
		$dirname = dirname($this->id);
		
		if(strpos($this->id, 'plugin=') === 0){
			
			$this->view = 'plugin';
		}elseif(strpos($dirname, 'archive') === 0){
			$this->view = 'archive';
		}elseif(strpos($dirname, '_fetch') === 0){
			$this->view = 'delicious';
		}elseif(strpos($dirname, '.') === 0){
			$this->view = 'splash';
		}else{
			$this->view = 'tags';
		}
		
	}
	
	/**
	* Fetches the content depending on the URL
	*  
	* return DOMObject
	* */
	private function getView(){
		$params = dirname($this->id);
		switch($this->view){
			case 'plugin':
				return $this->getPlugin($params);
				break;
			case 'archive':
				return $this->getArchive();
				break;
			case 'delicious':
				return $this->fetchDeliciousFeeds();
				break;
			case 'splash':
				return $this->getSplash();
				break;
			case 'tags':
				return $this->getTags();
				break;
			default:
				return $this->getSplash();
		}
	}
	
	/**
	 * getSplash
	 *
	 * By default returning newest links
	 * */
	private function getSplash(){
		$sql = bx_plugins_linklog_queries::splash($this->tablePrefix);
		$res = $this->getResultSet($sql);
		
		$sql = bx_plugins_linklog_queries::splashCount($this->tablePrefix);
		$this->totalItems = $this->getCount($sql);
		
		return $this->processLinks($res);
	}

	/**
	 * getArchive
	 *
	 * @param string like 2007-06-05 or 2007-06 or 2007
	 * @return preprocessed linklist
	 */
	private function getArchive(){
		// FIXME: thats a bit hacky here
		$this->pageTitle = 'Archive ('.current(explode("/",str_replace('archive/', '', mysql_escape_string($this->id)))).')';
		$sql = bx_plugins_linklog_queries::archive($this->id, $this->tablePrefix);		
		$res = $this->getResultSet($sql);
		
        $this->totalItems = $this->getCount(bx_plugins_linklog_queries::archiveCount($this->id, $this->tablePrefix));
        
		return $this->processLinks($res);
	}

	/*
	 *
	 * fetch the <deliciousName>-RSS passed via .configxml and locally inserts
	 * links from del.iciou.us/<deliciousName>
	 *
	 * FIXME: function way too long
	 * FIXME: move to linklog/delicious.php
	 */
	private function fetchDeliciousFeeds(){

		$deliciousName = $this->getParameter($this->path, 'deliciousname');
		if($deliciousName == "" || !$deliciousName){
			return;
		}

		define('MAGPIE_CACHE_DIR',BX_TEMP_DIR.'magpie/');
		include_once('magpie/rss_fetch.inc');

		// @todo pass them via configxml, for now, i am only testing :)
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

		foreach($links as $link){ // FIXME: only loop new items (possible by magpie?)

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

			$res = $editor->insertLink($data); // FIXME somehow previoisly check if the link already exists.

		}

		@unlink($this->cache4tags);
		$this->mapTags2Links();

	}

	private function simpleCleanUri($myuri){
		return str_replace(array('http://', 'rss'), array('',''), $myuri);
	}


	/**
	 * getLinksByTag
	 *
	 * @param $id
	 *
	 */
	private function getTags(){
		$querystring = bx_plugins_linklog_queries::getQuerystringFromId($this->id);
		// var_dump($querystring);
		// may be used for more caching: 
		$identifier = str_replace(" ", "+", $querystring);
		$this->pageTitle = ucfirst($identifier);
		
		
		$sql = bx_plugins_linklog_queries::linksByTag($querystring, $this->tablePrefix);
		$res = $this->getResultSet($sql);

		$sql = bx_plugins_linklog_queries::linksByTagCount($querystring, $this->tablePrefix);
		
		$this->totalItems = $this->getCount($sql);
		
		return $this->processLinks($res, $identifier);
	}

 

	/*
	 * @param array $vars(excludes => array(), 'exludes' => false || array)
	 * @todo: implement ;)
	 * */
	private function getMetaData(){
		
		
		return  "<meta><title>".$this->pageTitle."</title></meta>"; 
		// FIXME:


	}




	/**
	 * processLinks
	 *
	 * @param $links db-object containing link-data
	 * @param $meta xml-string with metadata being displayed in title
	 * @param string identifier for the given view (for caching (not yet implemented))
	 *
	 * */
	private function processLinks($res, $identifier = false){

		$map2tags = $this->mapTags2Links();
		
		$xml   = "<links>";
		
		// we should add some meta-data here ;)
		$xml .= $this->getMetaData();

		if($this->totalItems > $this->itemsPerPage){
		   $xml .= $this->getPager();
		}

		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$tags = $map2tags[$row['id']];
			$xml .= $this->getXmlForLink($row, $tags);
		}

		$xml .= "</links>";
		
		
		

		return $this->getDomObjectFromString($xml);


	}
    /*
    private function getPager($nrOfLinks){
    	
        // $totalPages = ceil($nrOfLinks / $this->itemsPerPage);
        
        $xml  = '<pager>';
        $xml .= '<total>' . $totalPages . '</total>';        
        $xml .= '<currentPage>' . $this->currentPage . '</currentPage>';                
        $xml .= '</pager>';
        
        return $xml;        
    }*/



	/**
	 *
	 * @param array data for a single link
	 * @param array of the tags for current link
	 * */
	private function getXmlForLink($row, $tags){
		$xml = "<link>";
		$xml .= "<id>".$row['id']."</id>";
		$xml .= "<title>".$row['title']."</title>";
		$xml .= "<description>".$row['description'] ."</description>";
		$xml .= '<archive>'.$this->getArchiveLinkFromTime($row['time']).'</archive>';
		$xml .= "<time>".$row['time']."</time>";
		$xml .= "<isotime>".$row['isotime']."</isotime>";
		$xml .= "<url>" . '<![CDATA[' .$row['url']."]]></url>";

		if($this->isLoggedIn){
			$xml .= "<edituri>".BX_WEBROOT_W."/admin/edit".$this->path."edit/".$row['id']."</edituri>";
			$xml .= "<deleteuri>".BX_WEBROOT_W."/admin/edit".$this->path."delete/".$row['id']."</deleteuri>";
		}
			
		$xml .= $this->getTagXmlForLink($tags);
		$xml .= "</link>";

		return $xml;

	}

	private function getTagXmlForLink($tags){
		$xml = "<tags>";
		if(is_array($tags)){
			foreach($tags as $t){
				$xml .= $this->getXmlForTag($t);
			}
		}
		$xml .= "</tags>";
		return $xml;

	}

	/**
	 * create a simple xml string for a single tag
	 * 
	 * @param array tag = array('name' => ..., 'fulluri' => ..., 'id' => ...)
	 * @return string
	 */
	private function getXmlForTag($t = array()){
		$xml  = "<tag>";
		$xml .= "<id>".$t['id']."</id>";
		$xml .= "<fulluri>".BX_WEBROOT_W.$this->path.$t['fulluri']."/</fulluri>";
		$xml .= "<name>".str_replace('&','&amp;',$t['name'])."</name>";
		$xml .= "</tag>";
		return $xml;
	}

	/**
	 * calls static plugin from extending class in /inc/bx/plugins/linklog/*
	 *
	 * currently, only tags exists
	 * @todo take this->path and $this->id
	 */
	private function getPlugin(){

		$plugin = substr($this->id,7);
		
		// this is a bit messy :)
		if ($pos = strpos($plugin,"(")) {
			$pos2 = strpos($plugin,")");
			$params = substr($plugin,$pos+1, $pos2 - $pos - 1);
			
			$plugin = substr($plugin,0,$pos);
			$params = explode(",",$params);
			// $params = str_replace(" ", '+', explode(",",$params));
		}  else {
			$params = array();
		}
		
		

		$plugin = "bx_plugins_linklog_".$plugin;
		
		$xml =  call_user_func(array($plugin,"getContentById"), $this->path, $this->id, $params,$this->tablePrefix);

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
		/*
		if($this->checkMapCache()){
			return $this->getMapCache();
		}
		*/
		$sql = bx_plugins_linklog_queries::tags($this->tablePrefix);
		// print $sql;
		$res = $this->getResultSet($sql, false);
		$tags = $this->prepareTagsArray($res);

		$sql = bx_plugins_linklog_queries::mapper($this->tablePrefix);
		$res = $this->getResultSet($sql, false);
		//var_dump($sql);
		$map = $this->doMap($tags, $res);

		$this->writeMapCache($map);

		return $map;

	}

	/*
	 * loop through all merges, to be able to fetch a category of the
	 * link in one catch
	 * 
	 * @param array all tags with its key as index
	 * @param mysql_result_set with all linkids and corresponding tagid
	 * @return array index is linkid, values are arrays with the tags
	 */
	private function doMap($tags, $res){
		
		$map = array();
		
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
			if(!array_key_exists($row['linkid'], $map)){
				$map[$row['linkid']] = array($tags[$row['tagid']]);
			}else{
				array_push($map[$row['linkid']], $tags[$row['tagid']]);
			}
		}
		
		return $map;
	}

	/*
	 * loop through all tags to create an array with its id as index
	 * 
	 * @param mysql_result_set with all the tags and id's
	 * @return array containing all tags with the id as their key
	 */
	private function prepareTagsArray($res){
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$tags[$row['id']] = $row;
		}
		return $tags;
	}
	
	/**
	* checks if the cache-file exists
	* 
	* @return boolean true if it exists
	* */
	private function checkMapCache(){
		return file_exists($this->cache4tags);
	}
	
	/**
	* gets the cache
	* 
	* @return array
	*/
	private function getMapCache(){
		return unserialize(file_get_contents($this->cache4tags));
	}
	
	/**
	* writes the cache
	* @return boolean true on success
	*/
	private function writeMapCache($map){
		return file_put_contents($this->cache4tags, serialize($map));		
	}
	
	/**
	 * Creates a fully linked date-view
	 *
	 * @param string
	 * @return string HTML with linked day, month and year to the archive-view
	 */
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

	//private function getPager(){
	private function getPager(){
		
		$totalPages = (int) ceil($this->totalItems / $this->itemsPerPage);
		
		$xml  = '<pager>';
		if(($this->currentPage + 1) <= $totalPages){
			$xml  .= '<next href="./?page='.($this->currentPage + 1) . '">'.($this->currentPage + 1).'</next>';
		}else{
			$xml .= '<next />';
		}		
		if(($this->currentPage - 1) >= 1){
			$xml  .= '<prev href="./?page='.($this->currentPage - 1) . '">'.($this->currentPage - 1 ).'</prev>';
		}else{
			$xml .= '<prev />';
		}
		
		$xml .= '<total>'.$totalPages.'</total>';
		$xml .= '<current>'.$this->currentPage.'</current>';
		
		/*
		$xml .= '<div class="blog_pager">';
		$xml .= '<span class="right">';
		$xml .= $prev;
		$xml .= $next;
		$xml .= '</span>';
		$xml .= $this->currentPage . '/' . $totalPages;		
		$xml .= '</div>';
		*/
		
		$xml .= '</pager>';
		
		return $xml;
	}
	
	
	/**
	* @param string SQL-Query
	* @return mysql_resultset
	* */
	private function getResultSet($sql, $addLimit = true){
		$start = ( $this->currentPage - 1 ) * $this->itemsPerPage ;
		
		// set the limit of the mysql result set
		if($addLimit){
			$sql .=  ' LIMIT '.$start . ', ' . $this->itemsPerPage;
		}
		
		$res = $this->db->query($sql);
		
		if (MDB2::isError($res)) {
			throw new PopoonDBException($res);
		}
		return $res;
	}

    private function getCount($sql){
    	
		$res = $this->db->query($sql);		
		if (MDB2::isError($res)) {
			throw new PopoonDBException($res);
		}
		
        return current($res->fetchRow());        
    }

	/**
	 * get a Dom Object from a wellformed XML-String
	 *
	 * @param string XML to be transformed to DOMObject
	 * @return DomDocument
	 * */
	private function getDomObjectFromString($xml){
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
	* Sets class variable isLoggedIn to true if user is currently logged in.
	* 
	* @return void 
	* */
	private function setLoginStatus(){
		$perm = bx_permm::getInstance();
		if($perm->isLoggedIn()){
			$this->isLoggedIn = true;
		}
		return;
	}

	
	private function getCurrentPage($getVars){
		
		if(array_key_exists('page', $getVars) && (int) $getVars['page'] > 1){
			return ((int) $getVars['page'] );
		}
		return 1;
	}
	
}

