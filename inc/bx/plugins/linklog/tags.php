<?php
/**
 * bx_plugins_linklog_tags
 * 
 * Supposed to handle everything to display the 
 * navigationtree of the linklogplugin
 * 
 * Inspired from  bx_plugins_blog_categories by Christian Stocker.
 * 
 * @author Alain Petignat
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
    * @return object DOM
    * 
    * @todo everything, first of all being able to get called correctly
    * 
    * */
    static function getContentById($path,$id,$params,$tablePrefix = "") {
    	
    	
        /*
         * This query gets tags as well as number of links marked with that tag
         * */
 		$sql = 'SELECT '.$tablePrefix.'linklog_tags.name, '. 
 				$tablePrefix.'linklog_tags.id, '.
 				$tablePrefix.'linklog_tags.fulluri, ' .
				'count( DISTINCT '.$tablePrefix.'linklog_links.id ) AS count '.
			 	'FROM '.$tablePrefix.'linklog_tags '.
				'LEFT JOIN '.$tablePrefix.'linklog_links2tags ON ' . 
				$tablePrefix.'linklog_tags.id = '.$tablePrefix.'linklog_links2tags.tagid ' .
				'LEFT JOIN '.$tablePrefix.'linklog_links ON ' .
				$tablePrefix.'linklog_links2tags.linkid = '.$tablePrefix.'linklog_links.id '.
				'GROUP BY id '.
				'ORDER BY count DESC';        
        
        $res = $GLOBALS['POOL']->db->query($sql);
        if (MDB2::isError($res)) {
            // throw error
            throw new PopoonDBException($res);
            // echo "error";
            exit;
         }

        $tags = array();
        
        if (isset($params[0])) {
        	// the + in the url is interpreted as a " " from the browser, so we fix it here
        	$params[0] = str_replace(' ', '+', $params[0]); 

        	// something like foo+bar/index.html - we want foo+bar
            $tags = explode( '+', substr( $params[0], 0, strrpos($params[0],"/") ) );
        }
         

        $i = 1;	// for the order...

		$xml = "<items>";
        
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        	$title = $uri = '';
            if($row['count'] > 0){    // dont display tags without a link NOTE: may be done in db?

                if (in_array($row['fulluri'], $tags) ) {
                    $xml .= '<collection selected="selected">';
                    $title = $row['title'];
                    $uri = BX_WEBROOT_W.$path.$row['fulluri']."/";
                } else {
                    $xml .= '<collection selected="all">';
                }            
                
                $xml .= '<title>'.$row['name'].' ('.$row['count'].') </title>';            
                $xml .= '<uri>'.BX_WEBROOT_W.$path.$row['fulluri'] . '/'.'</uri>';                 
                $xml .= '<display-order>'.$i.'</display-order>';            
                $xml .= '</collection>';            
                 
                $i++;
            }
            
        }
        
        $xml .= "</items>";

		$title = 'foobar';
		
        $xml2   = '<collection selected="all">';
        $xml2  .= '<title>'.$title.'</title>';
        $xml2  .= '<title>Foobar</title>';
        $xml2  .= '<uri>'.$uri.'</uri>';      
        $xml2  .= $xml;

        $xml2  .= '</collection>'; 
        
        $dom = new DomDocument();
        
        if (function_exists('iconv')) {
            $xml2 =  @iconv("UTF-8","UTF-8//IGNORE",$xml2);
        }
        
        $dom->loadXML($xml2);
        
        return $dom;
                    
    }



}
?>
