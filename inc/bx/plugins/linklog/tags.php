<?php
/**
 * bx_plugins_linklog_categories
 * 
 * Supposed to handle everything to display the navigationtree of the link-
 * plugin
 * 
 * Inspired from  bx_plugins_blog_categories by Christian Stocker.
 * 
 * @author Alain Petignat
 * @todo returning a dom-domcument according to the call by linklog.xsl
 * 
 * */
class bx_plugins_linklog_tags {
    /**
    * static function getContentById
    * 
    * @param string $path
    * @param string $id
    * @param array $params
    * @param int $parent
    * @param string $tablePrefix
    * @return object $dom Dom Document to be parsed by xsl (?) 
    * 
    * @todo everything, first of all being able to get called correctly
    * 
    * */
    static function getContentById($path,$id,$params,$tablePrefix = "") {
        
        if (isset($params[0])) {
            $lastslash = strrpos($params[0],"/");
            $tag = substr($params[0],0,$lastslash);
        } else {
            $tag = "";
        }
                
        
        // $query = "SELECT * FROM '.$tablePrefix.'linklog_tags";
        
 		$query = 'SELECT '.$tablePrefix.'linklog_tags.name, '.$tablePrefix.'linklog_tags.id, '.$tablePrefix.'linklog_tags.fulluri, count( DISTINCT '.$tablePrefix.'linklog_links.id ) AS c
			 	FROM '.$tablePrefix.'linklog_tags
				LEFT JOIN '.$tablePrefix.'linklog_links2tags ON '.$tablePrefix.'linklog_tags.id = '.$tablePrefix.'linklog_links2tags.tagid
				LEFT JOIN '.$tablePrefix.'linklog_links ON '.$tablePrefix.'linklog_links2tags.linkid = '.$tablePrefix.'linklog_links.id
				GROUP BY id
				ORDER BY c DESC';        
        
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            // throw error
            throw new PopoonDBException($res);
            // echo "error";
            exit;
         }     
            
        $i = 1;
        
        $xml = "<items>";
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			if($row['c'] > 0){
	            if ($tag === $row['fulluri']) {
	                $xml .= '<collection selected="selected">';
	                $title = $row['title'];
	                $uri = BX_WEBROOT_W.$path.$row['fulluri']."/";
	            } else {
	                $xml .= '<collection selected="all">';
	            }            
	            
	            $xml .= '<title>'.$row['name'].' ('.$row['c'].') </title>';            
	            $xml .= '<uri>'.BX_WEBROOT_W.$path.$row['fulluri'].'</uri>';                 
	            $xml .= '<display-order>'.$i.'</display-order>';            
	            $xml .= '</collection>';            
	             
	            $i++;
        		}
            
        }
        
        $xml .= "</items>";


        $xml2   = '<collection selected="all">';
        $xml2  .= '<title>'.$title.'</title>';
        $xml2  .= '<uri>'.$uri.'</uri>';      
        $xml2  .= $xml;
        $xml2  .= '</collection>'; 
        
        // print $xml2;
        
        $dom = new DomDocument();
        
        if (function_exists('iconv')) {
            $xml2 =  @iconv("UTF-8","UTF-8//IGNORE",$xml2);
        }
        
        $dom->loadXML($xml2);
        

       file_put_contents("/Library/WebServer/Documents/info/coll.xml",$xml2);
        
 
        
        return $dom;
                    
    }



}
?>
