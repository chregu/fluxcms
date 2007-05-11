<?php
/*
-config.xml
<?xml version="1.0"?>
<bxcms xmlns="http://bitflux.org/config">

    <plugins>
        <parameter name="xslt" type="pipeline" value="allposts.xsl"/>
        <plugin type="blog_allposts">
            <!-- add blogpath here please if not set xml will not output the uri-->
            <parameter name="blogpath" value="/blog/"/>
            <!-- add blogid here please(needed) if only one blog '1' should be default -->
            <parameter name="blogid" value="1"/>
         </plugin>
    </plugins>
</bxcms>

$Id$

*/
class bx_plugins_blog_allposts extends bx_plugin implements bxIplugin {
    
    static public $instance = array();
    
    private $db          = null;
    private $tablePrefix = null;
    private $cacheFile   = '';
    
    private $blogid     = 1;
    private $blogpath   = null;    
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_blog_allposts($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
        $this->cacheFile = BX_TEMP_DIR."/". $this->tablePrefix."-".$this->blogid."-allposts.xml";        
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        return $name.'.'.$this->name;
    }
    
    public function getContentById($path, $id){
        
        $this->blogid   = $this->getParameter($path,"blogid");        
        $this->blogPath = $this->getParameter($path,"blogpath");
       
    	if(!$this->cacheIsValid()){
            $tgs = $this->getAllTags();
            $cts = $this->getAllCats();
            $xml = $this->getFullXmlAsString($tgs, $cts);
            $this->writeCache($xml);
    	}else{
    		$xml = $this->getCache();
    	}

    	$dom = new DomDocument();
        $dom->loadXML($xml);
 
        return $dom;
    }    
    
    private function cacheIsValid(){
        if(!file_exists($this->cacheFile)){
            return false;    
        }elseif((time() - filemtime($this->cacheFile)) > 3600){ // cache for an hour
            return false;
        }
        return true;
    }
    
    private function getAllTags(){
        	$sql = 'SELECT path,tag FROM '.$this->tablePrefix.'properties2tags 
        	           LEFT JOIN '.$this->tablePrefix.'tags 
        	           ON '.$this->tablePrefix.'properties2tags.tag_id='.$this->tablePrefix.'tags.id       
        	          WHERE tag != ""';

         	$res = $this->db->query($sql);

        	while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
        		// we cut the leading slash and the ending .html away from array-key:
        		$tgs[substr($row['path'], 1, -5)][] = '<a href="' . $this->blogPath . 'archive/tag/' . $row['tag'] .'/">'.$row['tag'].'</a>';
        	} 

            return $tgs;
        
    }

    private function getAllCats(){
        
        $catqry = "SELECT blogposts_id,fulluri,name 
                FROM ".$this->tablePrefix."blogposts2categories 
                RIGHT JOIN ".$this->tablePrefix."blogcategories 
                ON  blogcategories_id=".$this->tablePrefix."blogcategories.id 
                WHERE blog_id=".$this->blogid;

        $res = $this->db->query($catqry);
       if (MDB2::isError($res)) {
           return "<error/>";
       }
        
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        	$cts[$row['blogposts_id']][] = '<a href="'.$this->blogPath.''.$row['fulluri'].'/">'.$row['name'].'</a>';
        }        

        return $cts;
    }
    
    private function getFullXmlAsString($tgs, $cts){
        $query = "SELECT 
                    id as post_id,
                    post_title as title, 
                    post_uri, 
                    date_format(post_date, '%Y%m%d') AS datecomp, 
                    date_format(post_date, '%Y') as year, 
                    date_format(post_date, '%m') as month, 
                    date_format(post_date,'%d') as day, 
                    date_format(post_date, '%Y/%m/%d') as uridate, 
                    date_format(post_date, '%H:%i') as date 
                    FROM ".$this->tablePrefix."blogposts 
                    WHERE post_status=1 
                    AND blog_id = " . $this->blogid . " 
                    ORDER BY post_date DESC";
        
        
        $res = $this->db->query($query);
       
        if (MDB2::isError($res)) {
           return "<error/>";
       }
        
    	$curYear = null;
    	$curDay = null; 
    	$curMonth = null;

        $xml = '<html><head></head><body>';

        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            if($row['year'] < $curYear || is_null($curYear)){
        		    $curYear    = $row['year'];
        		    $curMonth   = null;

        		    $year       = '<a href="'.$this->blogPath.'archive/'.$row['year'].'/">'.$row['year'].'</a>'; 
        		    $xml       .= '<h2 class="post_title">'.$year.'</h2>';
        	}    

            if($row['month'] < $curMonth || is_null($curMonth)){
                $curYear    = $row['year'];
                $curMonth   = $row['month'];
                $curDay     = null;         		    
        	        
                $month      = '<a href="'.$this->blogPath.'archive/'.$row['year'].'/'.$row['month'].'/">'.$row['month'].'</a>'; 
                
                $xml       .= '<h3 class="post_title">'.$month.'.'.$year.'</h3>';
            }   

            if($row['day'] < $curDay || is_null($curDay)){
            	$curDay     = $row['day'];
            	$day        = '<a href="'.$this->blogPath.'archive/'.$row['uridate'].'/">'.$row['day'].'</a>'; 
            	$xml .= '<h4>'.$day.'.'.$month.'.'.$year.'</h4>';
            }    

            $xml .= '<p class="post_content">';
            $xml .= '<em>'.$row['date'].'</em> - ';
            $xml .= '<a href="'.$this->blogPath.'archive/'.$row['uridate'].'/'.$row['post_uri'].'.html">' . $row['title'].'</a>';
            $xml .= ' in '.implode(', ', $cts[$row['post_id']]);
            if(is_array($tgs[$row['post_uri']])){
                $xml .= ' (tags: '.implode(', ', $tgs[''.$row['post_uri']]).")";
            }
            $xml .= '</p>';
        }
        $xml .= '</body></html>';

        return $xml;
        
    }
    
    private function writeCache($xml){
        return file_put_contents($this->cacheFile, $xml);        
    }

    private function getCache(){
        return file_get_contents($this->cacheFile);
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
}
?>