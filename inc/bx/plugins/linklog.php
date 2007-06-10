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
	/*
	 * The table names
	 */
	protected $linksTable 	    = "linklog_links";
	protected $tagsTable		    = "linklog_tags";
	protected $links2tagsTable 	= "linklog_links2tags";

	/*
	 * database
	 */
	protected $db = null;
	protected $tablePrefix = NULL;
	protected $cache4tags;
	protected $isLoggedIn = false;

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

		$dirname = dirname($id);

		$this->path=$path;

		// FIXME: implement strategy
		if (strpos($id,"plugin=") === 0) {
			return $this->callInternalPlugin($id, $path);
		}

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
		$sql = bx_plugins_linklog_queries::splash($this->tablePrefix);
		$res = $this->getResultSet($sql);
		return $this->processLinks($res);
	}

	/**
	 * getArchive
	 *
	 * @param string like 2007-06-05 or 2007-06 or 2007
	 * @return preprocessed linklist
	 */
	private function getArchive($path){
		$sql = bx_plugins_linklog_queries::archive($path, $this->tablePrefix);
		$res = $this->getResultSet($sql);
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
	 * getLinksByTag
	 *
	 * @param $id
	 *
	 */
	private function getLinksByTag($id){
		$sql = bx_plugins_linklog_queries::linksByTag($id, $this->tablePrefix);
		$res = $this->getResultSet($sql);
		$meta = $this->getMetaData($vars);
		return $this->processLinks($res, $meta);
	}



	/*
	 * @param array $vars(excludes => array(), 'exludes' => false || array)
	 * @todo: implement ;)
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




	/**
	 * processLinks
	 *
	 * @param $links db-object containing link-data
	 * @param $meta xml-string with metadata being displayed in title
	 *
	 * */
	private function processLinks($links, $meta = false){

		$map2tags = $this->mapTags2Links();
		$xml   = "<links>";
		
		$xml .= $this->addMetaData($meta);

		while($row = $links->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$tags = $map2tags[$row['id']];
				
			$xml .= $this->getXmlForLink($row, $tags);
		}

		$xml .= "</links>";

		return $this->getDomObjectFromString($xml);


	}

	private function addMetaData($string){
		if(is_string($meta)){
			return $meta;
		}
		return '';
	}


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
		$xml .= "<tag>";
		$xml .= "<id>".$t['id']."</id>";
		$xml .= "<fulluri>".BX_WEBROOT_W.$this->path.$t['fulluri']."</fulluri>";
		$xml .= "<name>".str_replace('&','&amp;',$t['name'])."</name>";
		$xml .= "</tag>";
		return $xml;
	}

	/**
	 * calls static plugin from extending class in /inc/bx/plugins/linklog/*
	 *
	 * currently, only tags exists
	 */
	private function callInternalPlugin($id, $path){

		$plugin = substr($id,7);
		// this is a bit messy :)
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
		if($this->checkMapCache()){
			return $this->getMapCache();
		}

		$sql = bx_plugins_linklog_queries::tags($this->tablePrefix);
		$res = $this->getResultSet($sql);
		$tags = $this->prepareTagsArray($res);

		$sql = bx_plugins_linklog_queries::mapper($this->tablePrefix);
		$res = $this->getResultSet($sql);
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

	
	/**
	* @param string SQL-Query
	* @return mysql_resultset
	* */
	private function getResultSet($sql){
		$res = $this->db->query($sql);
		if (MDB2::isError($res)) {
			throw new PopoonDBException($res);
		}
		return $res;
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

}

