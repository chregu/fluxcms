<?php

class bx_plugins_customsearch extends bx_plugins_search2 implements bxIplugin {
    
    /**
    * a static var to to save the instances of this plugin
    */
   
    
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_customsearch($mode);
        } 
        return self::$instance[$mode];
    }
    
    
    protected function __construct() {
       $this->db = $GLOBALS['POOL']->db; 
    }
     
    
    protected function getPages($search,$tag) {
        $pages =  array();
        $options = array ('searchStart' => 0 , 'searchNumber' => 10,
          'lang' => $GLOBALS['POOL']->config->getOutputLanguage()
        );
       
        $p['fulltext'] = $this->getFulltextPages($search,$tag,$options);
        $p['news'] = $this->getNews($search,$tag,$options); 
//      $p['bookmarks'] = $this->getBookmarks($search,$tag,$options);   // Please uncoment this line as well, if you do not want to use the Bookmarks Search.
        
        return $p;
    }
    
    protected function getNews($search, $tag, $options) {
    	$tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $search = explode(" ",$search);
        $search = "+".implode(" +",$search);
        $query = "select id, post_uri, post_title, post_content, changed, sum(MATCH ( post_content, post_title) AGAINST (".$this->db->quote($search)." IN BOOLEAN MODE)) as cnt from ".$tablePrefix."blogposts where
        post_status = 1 and MATCH ( post_content, post_title) AGAINST (".$this->db->quote($search)." IN BOOLEAN MODE) group by post_uri order by cnt, post_date DESC LIMIT 10";
        $res = $this->db->query($query);
         
	        $ids = array();
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $id = $row['id'];
            $ids[$id]['title'] =  $row['post_title'];
            $ids[$id]['url'] = '/news/'.$row['post_uri'].".html";
            $ids[$id]['lastModified'] = $row['changed'];
            $ids[$id]['text'] = bx_helpers_string::truncate(strip_tags($row['post_content']));
            $ids[$id]['id'] = $id;
            $ids[$id]['cnt'] = $row['cnt'];
        }
        
        return array("entries" =>$ids); 
      }
     
    
/* 	The Bookmarksearch starts below. It searches the Bookmarks' titles.  
 */

  
      protected function getBookmarks($search, $tag) {
  		if (strlen($_GET['q']) < 4) { }
  		else {
  		
  			$xml = simplexml_load_file(BX_DATA_DIR.'/bookmarks/index.en.xhtml');
			$ids = array();
			$id = 0 ;
					
  				if (substr($_GET['q'], -1) == "*") {
  		
  					$searchterm = strToLower(trim(rtrim($_GET['q'], "*")));
					$bookmarks = $xml->xpath("//bookmark[contains(translate(title, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$searchterm')]");


						foreach ( $bookmarks as $bookmark ){
							if (preg_match("/\b".$searchterm."/i", $bookmark->title)){

							$id++ ; 
		    				$ids[$id]['title'] =  htmlspecialchars($bookmark->title) ;
            				$ids[$id]['url'] = htmlspecialchars($bookmark['href']);
            				$ids[$id]['id'] = $id;       
            				$ids[$id]['text'] = '';
            				$ids[$id]['cnt'] = '';
            				$ids[$id]['lastModified'] = '';
            				}
            				else {}
						}
				}
				
				else {
  					$searchterm = strToLower(trim($_GET['q']));
					$bookmarks = $xml->xpath("//bookmark[contains(translate(title, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$searchterm')]");

						foreach ( $bookmarks as $bookmark ){
							if (preg_match("/\b".$searchterm."\b/i", $bookmark->title)){

							$id++ ; 
				    		$ids[$id]['title'] =  htmlspecialchars($bookmark->title) ;
        		    		$ids[$id]['url'] = htmlspecialchars($bookmark['href']);
       			     		$ids[$id]['id'] = $id;       
        		    		$ids[$id]['text'] = '';
        		    		$ids[$id]['cnt'] = '';
           			 		$ids[$id]['lastModified'] = '';
           			 		}
           			 		else {}
           			 		
						}		
				}
  		return array("entries" =>$ids); 
  	  	}
  	  }

/* END of Bookmarksearch. */

}
