<?php
/**
 * class bx_editors_linklog
 * 
 * Class for editing links. 
 * 
 * interface bxIeditor {
 * 		public function getDisplayName();
 * }
 * 
 * @todo validate data correctly
 * @todo insert the bookmarklet
 * @todo replace dbtables with variables
 * @todo check if it is possible to use different xsl as editors (light editor)
 * 
 * */
class bx_editors_linklog extends bx_editor implements bxIeditor {    
    
    /**
    The table names
    */
    public $linksTable 		= 'linklog_links';
    public $tagsTable 		= 'linklog_tags';
    public $links2tags 		= 'linklog_links2tags';   
    
    // data for a link:
    protected $id 			= '';
    protected $url 			= '';
    protected $description 	= '';
    protected $title 			= '';
    protected $tags 			= array();    
    
     /*
     * database
     */
	protected $tablePrefix;
	protected $db;	
    protected $tagCacheFile;
    
    public function __construct(){
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;    
    }
    
    public function getPipelineName() {
        return 'linklog';
    }
    
    public function getDisplayName() {
        return 'Linklog Editor';
    }	
    
    /**
     * handlePost
     * 
     * @param String path
     * @param String id
     * @param Array data
     * 
     * @todo setPostData should be renamed to checkAndSetPostData and do validation
     *  */
    public function handlePOST($path, $id, $data) {
		$this->setPostData($data);
		
		/* 
		 * the id is only present, when an empty form is being submitted.
		 * otherwise its passed via a hidden field.
		 */ 
        if($data['id'] == ''){
            $linkid = $this->insertLink();
            header('Location: ./edit/'.$linkid);
        }else{
            $this->updateLink($data['id']);
        }
     }
     
	 /**
      * getEditContentById
      * 
      * The "actual being called"-method when returning content
      * 
      * @param String id $_GET-Value of URL, e.g /linklog/edit/1
      * @return datatype DomDocument to be processed by xsl
      *  	  
      * */
     public function getEditContentById($id) {
        /* for debugging:
	        $this->debug($_GET, "get");        
	        $this->debug($id, "id");
        */
        
        // $this->debug($this->tablePrefix, "this table prefix");
        
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
		
		// $this->debug($this->tablePrefix, "table prefix");
		
		$path = $_GET['path'];
		$myPath = str_replace("admin/edit/linklog/","",$path);
		
        /* default behaviour is to return an empty document */
        if($myPath === ""){
            return $this->getEmptyLink();
		
        /* 
         * editing an existing link: 
         * id:../edit/$id
         */
		}elseif(substr($myPath,0,4) == "edit"){
            $tmp = explode("/",$myPath);
            $linkid = end($tmp) + 0;
            
            return $this->getSingleLink($linkid);
		
		}elseif(substr($myPath,0,6) == "delete"){
            /**
             * deleting a link and redirecting to editors-root
             * id:../delete/$id
             */
            $tmp = explode("/",$myPath);
            $linkid = end($tmp) + 0;
                        
            if($this->deleteLink($linkid) === true){
                header('Location: ../?deleted=1');
            }

		}else{
			$myPath = '';
		}
     }
     
    /**
     * dataIsValid
     * 
     * this function is never used yet
     *  
     * @todo: implement this function and add return proper error to user
     * 
     */
    private function dataIsValid(){
		if($this->title == ''){		
			return false;
		}
		if($this->url == ''){		
			return false;
		}
		return true;
    } 
	
	/**
	 * setPostData
     * 
     * does basic correction of data and sets them to locally declared
     * parameters
     * 
     * @param Array  $_POST-data
     * @return void
     * 
     * passing the postdata to local variables and basic check and set some values - pass tags to array 
	 * */
    private function setPostData($data){
	 	$this->title 		= utf8_encode(trim($data['title']));
	 	$this->url 			= utf8_encode(str_replace("&","&amp;",trim($data['url'])));

		// check description
	 	$this->description 	= utf8_encode(trim($data['description']));
		if($this->description == ''){
			$this->description = 'no description';
		}

		// check tags
	 	$this->tags = explode(' ',trim($data['tags']));
		if($this->tags[0] == ''){
			$this->tags = array('default');		
		}	 	
    } 

	/**
	 * getEmptyLink
	 * 
	 * return empty element, may use values passed by $_GET (when using bookmarklet)
	 * 
	 * @return Object an empty link as a domdocument
	 * */
    private function getEmptyLink () {
        /* to fill in the http into the empty link */
        if($_GET['url']){
            $url = $_GET['url'];
        }else{
            $url = 'http://';
        }
        
        $xml = '<linklog>' .
                '<link>' .
                '<id />' .
                '<title>'.$_GET['name'].'</title>' .
                '<description>' . $_GET['description'] .'</description>'.
                '<time />' .
                '<url>'.$url.'</url>' .
                '<tags />' .
                '</link>' ;
        	$xml .= $this->getTags();                
         $xml.= '</linklog>';

                
                $dom = new DomDocument();
                if (function_exists('iconv')) {
                    $xml =  @iconv('UTF-8','UTF-8//IGNORE',$xml);
                }
                $dom->loadXML($xml);
              //  print htmlentities($dom->saveXML());
                return $dom;                   
	}
    
    /**
     * get single link out 
     * 
     * @param linkid int id of link to get 
     * 
     */
    private function getSingleLink ($linkid) {
                        
            $row = $this->getSingleLinkFromDb($linkid);
            if($row === false){
               return  $this->getEmptyLink();
            }
        
            $tagstring = $this->getTagsForLink($linkid);
            
            /*
             * put together the dom for returning
             * @todo: xml-schema
             * */
            $xml  = '<linklog>';
            $xml .= '<link>';
            $xml .= '<id>'.$row['id'].'</id>';
            $xml .= '<title>'.$row['title'].'</title>';
            $xml .= '<description>'.$row['description'] .'</description>';
            $xml .= '<time>'.$row['time'].'</time>';
            $xml .= '<url>'.str_replace('&','&amp;',$row['url']).'</url>';
            $xml .= '<tags>'.$tagstring.'</tags>';
            $xml .= '</link>';
            $xml .= $this->getTags();
            $xml .= '</linklog>';
            

            
            
            $dom = new DomDocument();
            if (function_exists('iconv')) {
                $xml =  @iconv('UTF-8','UTF-8//IGNORE',$xml);
            }
            
            $dom->loadXML($xml);
            return $dom;            		
	}

    /**
     * insertLink
     * 
     * inserts a link to the db with the entry to the merge-table
     * @return id of inserted link  
     * 
     * */
    private function insertLink () {

        /*
         * add  link to db, get back id
         * @todo make own private method
         */
        $query = 'insert into '.$this->tablePrefix.$this->linksTable.' (title, description, url, status, time)' .
                 'VALUES ("'.$this->title.'", "'.$this->description.'", "'.$this->url.'", 1, now())';
        
        $res = $this->db->query($query);    
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }

        // get back id:
        $query = 'select id from '.$this->tablePrefix.$this->linksTable.' order by id DESC LIMIT 0,1';
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
               throw new PopoonDBException($res);
        }
        
        $lid = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        $linkid = $lid['id'];
        
        /*
         * do the tags:
         */
        $tag_array = $this->checkAndGetTagIds($this->tags);
        foreach($tag_array as $tagid){
            $this->insertLinks2Tags($tagid, $linkid);            
        }
        return $linkid;		
	}

    /**
     * fetches the tags for a link
     * 
     * @param int linkid id of the link to fetch tag for
     * @param boolean asArray true if tags should be returned as an array
     * @return String Tags as a simple String
     * @return Array Tags as an array
     * 
     */
    private function getTagsForLink($linkid, $asArray = false){
            /*
             * fetch tags
             * @todo: make private method
             */
            $query = 'SELECT '.$this->tablePrefix.'linklog_tags.name, '.$this->tablePrefix.'linklog_tags.id from '.$this->tablePrefix.'linklog_tags ' .
                     'LEFT JOIN '.$this->tablePrefix.$this->links2tags.' ON '.$this->tablePrefix.'linklog_tags.id = '.$this->tablePrefix.$this->links2tags.'.tagid ' .
                     'WHERE '.$this->tablePrefix.$this->links2tags.'.linkid='.$linkid; 
            $res = $this->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }
            while($tag = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){              
                $tagstring .= "".str_replace('&','&amp;',$tag['name'])." "; 
                
                $tagarray[$tag['name']]  = $tag['id']; 
                
                
            }
            if($asArray == false){
                return trim($tagstring);
            
            }else{
                return $tagarray;
            }    
    }

    /**
     * Simple debug function:
     * */
    private function debug($data, $name = "var: "){
         if(is_array($data)){
                print '<pre>';
                print "<b>$name:</b>\n";     
                print_r($data);
                print "</pre>";
         }elseif(is_string($data)){
                print "<pre><b>$name:</b> $data\n</pre>";
         }
    }

    /**
	* 
    * @todo documentation
    * 
    */
    private function updateLink($linkid){
        
        $linkid = $linkid + 0;
        
        /*
         * update linktable
         */
        $query = 'UPDATE '.$this->tablePrefix.$this->linksTable.' SET ' .
                 'title="'.$this->title.'", ' .
                 'description="'.$this->description.'", ' .
                 'url="'.$this->url.'"' .
                  'WHERE id='.$linkid; 
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
        }  
        
        /*
         * tags are a bit more complicated
         * 
         * easiest way is to kill existent and renew them
         * maybe first check if they have changed at all...
         */
        $oldTags         = $this->getTagsForLink($linkid, true);    
        $newTagsExisting = $this->getExistingTags($this->tags);        
        $query = 'delete from '.$this->tablePrefix.$this->links2tags.' where linkid='.$linkid;
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
        }  
        $tag_array = $this->checkAndGetTagIds($this->tags);
        foreach($tag_array as $tagid){
            $this->insertLinks2Tags($tagid, $linkid);            
        }
        
        return true;
    }

    /**
     * 
     * @todo documentation
     */
    private function getSingleLinkFromDb($linkid){
            /*
             * fetch data from link
             * @todo make private method
             */
            $query = 'SELECT * from '.$this->tablePrefix.$this->linksTable.' where id='.$linkid;
            $res = $this->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
            }   
            $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
            
            // someone produced shit when this happens:
         
            if($res->numRows() === 0){
                return false;
            }else{
                return $row;        
            }   
    }
    
    /**
     * function checkAndGetTagIds
     * 
     * does the main checking of the tags. if a tag already exsists, it fetches
     * its id, otherwise, it inserts the tag to the db and also returns the id
     * of the newly inserted tag
     * 
     * @param array tags in an array 
     * @return array with ids of corresponding tags for links from db 
     * 
     * */
    private function checkAndGetTagIds($tags){
        
        /* get array of already existing tags of the passed tag-array */
        $tagid = $this->getExistingTags($tags);
        
        /*
         * create the tags in the db if they do not exist yet
         * @todo private method
         */
        if((count($tagid) < count($tags)) || ( $tagid == false )){
            /*
             * loop through tags given by post
             */
            foreach($tags as $tag){
                /*
                 * compare with previous created array of existing tags
                 */
                // use the fulluri as index
                $fulluri = bx_helpers_string::makeUri($tag);
                if(!$tagid[$fulluri]){
                    $tagid[$fulluri] = $this->insertSingleTag($tag);
                } // end if must insert new tag to db
            }     // end foreach tags
        }         // end if count of db check < count of tags
        return $tagid;
    }
    
    /**
     * function getExistingTags
     * 
     * @return Array 
     * 
     * */
    private function getExistingTags($tags){
        /*
         * check which tags are already in the db
         */
         $where = '(';
        foreach($tags as $fulluri){
            $where .= '"'.bx_helpers_string::makeUri($fulluri) .'", ';
        }
        $where = substr($where, 0, -2);
        $where .= ')';
        $query = 'select id, fulluri from '.$this->tablePrefix.'linklog_tags where fulluri in '.$where;
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
               throw new PopoonDBException($res);
        }
        
        /*
         * put them in an array $tagid (
         * @todo: make private function returning array
         */
        if( $res->numRows() > 0 ){        
                while($row = $res->fetchRow()){
                    $tagid[$row[1]] = $row[0];
                }   
                return $tagid;
        }else{
            return false;
        }    
    }
    
    /**
     * insertSingleTag
     * 
     * inserts a tag to the db
     * 
     * @param String Tag
     * @return int Id of just inserted tag
     * 
	 */
    private function insertSingleTag($tag){
        // make a clean uri
        $fulluri    = bx_helpers_string::makeUri($tag);
        
        $query      =   'INSERT INTO '.$this->tablePrefix.'linklog_tags'  .
                        ' (name, fulluri)'  .
                        ' VALUES ("'.$tag.'", "'.$fulluri.'")';
                        
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
            exit;
        } 

        $query      = 'SELECT id, name FROM '.$this->tablePrefix.'linklog_tags ORDER BY id DESC LIMIT 0,1';                    
        $res = $this->db->query($query);
        if (MDB2::isError($res)) {
              throw new PopoonDBException($res);
              exit;
        } 
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);    
                    
        return $row['id'];
    }
     
    /**
     * insertLinks2Tags
     * 
     * inserts the values to the merge-table
     * 
     * @param int tagid - id of the tag in db
     * @param int linkid - id of link
     * @return boolean true on success
     * 
     * */
    private function insertLinks2Tags($tagid, $linkid){
            $query = 'INSERT INTO '.$this->tablePrefix.$this->links2tags.' (linkid, tagid) '  .
                     'VALUES ('.$linkid.', '.$tagid.')';
            
            $res = $this->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
                exit;
            }            
            return true;
    }

    /**
     * deleteLink
     * 
     * deletes a single link in the db plus the corresponding entries in the
     * merge table
     * 
     * @param int linkid id of the link to delete
     * @todo garbagecollection on tags-table
     */
    private function deleteLink($linkid){
         // print $linkid;
         $queries[] = 'DELETE FROM '.$this->tablePrefix.$this->links2tags.' WHERE linkid='.$linkid;
         $queries[] = 'DELETE FROM '.$this->tablePrefix.$this->linksTable.' WHERE id='.$linkid;
         
         foreach($queries as $query){
            $res = $this->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
                exit;
            }           
         }
         return true;
    }
    
    private function getTags(){
    		$query = 'SELECT '.$this->tablePrefix.'linklog_tags.name, '.$this->tablePrefix.'linklog_tags.id, count( DISTINCT '.$this->tablePrefix.$this->linksTable.'.id ) AS c
			 	FROM '.$this->tablePrefix.'linklog_tags
				LEFT JOIN '.$this->tablePrefix.$this->links2tags.' ON '.$this->tablePrefix.'linklog_tags.id = '.$this->tablePrefix.$this->links2tags.'.tagid
				LEFT JOIN '.$this->tablePrefix.$this->linksTable.' ON '.$this->tablePrefix.$this->links2tags.'.linkid = '.$this->tablePrefix.$this->linksTable.'.id
				GROUP BY id
				ORDER BY c DESC';
            $res = $this->db->query($query);
            if (MDB2::isError($res)) {
                throw new PopoonDBException($res);
                exit;
            }
            
            $xml = '<tags>';    			
            while($tag = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){ 
				$xml .= "<tag><name>$tag[name]</name><id>$tag[name]</id><numberentries>$tag[c]</numberentries></tag>";
            }		
			$xml .= '</tags>';
			
			return $xml;
    }
    
    

}

?>
