<?php

class bx_plugins_linklog_queries {
	
	const linksTable = 'linklog_links';
	const tagsTable  = 'linklog_tags';
	const mapTable   = 'linklog_links2tags';
	
	/**
	*
	**/
	public static function splash($prefix){
		$sql = 	'SELECT distinct links.*,' .
				'DATE_FORMAT(links.time, "%Y-%m-%dT%H:%i:%SZ") as isotime '.
				'FROM '.$prefix.self::linksTable.' links ' .
				'ORDER BY links.time DESC';
		return $sql;
	}

	public static function splashCount($prefix){
		$sql = 	'SELECT count(distinct links.id) '.
				'FROM '.$prefix.self::linksTable.' links';
		return $sql;
	}
	
	
	public static function archive($id, $prefix){
		$sql = 'SELECT DISTINCT links.*, ' .
				'DATE_FORMAT(links.time, "%Y-%m-%dT%H:%i:%SZ") as isotime '.
				self::archiveBaseQuery($id, $prefix). 
		        'ORDER BY links.time DESC';		
				
		return $sql;		
	} 
	
	public static function archiveCount($id, $prefix){
		$sql = 'SELECT count(distinct links.id) as count '.
				self::archiveBaseQuery($id, $prefix);	    
				return $sql;
	}
	
	public static function linksByTag($querystring, $prefix){
		
		$vars        = self::splitQuerystringToParams($querystring);
		
		$sql  		 = self::getBasicLinkQuery($prefix);
		$sql 		.= self::getWhereIncludesTags($vars['includes'], $prefix);

		if($vars['excludes']){
			$sql .= self::linkByTagsExcludes($vars['excludes'], $prefix);
		}

		$sql .= self::linkByTagGroupBy();
		$sql .= self::linkByTagHavingCount($vars['includes']);
		$sql .= self::getBasicLinkQueryOrderBy();
		
		return $sql;
	}	
	
	public static function linksByTagCount($querystring, $prefix){
	    $vars        = self::splitQuerystringToParams($querystring);
		$sql  		 = self::getBasicLinkQueryCount($prefix);
		$sql 		.= self::getWhereIncludesTags($vars['includes'], $prefix);
		if($vars['excludes']){
			$sql .= self::linkByTagsExcludes($vars['excludes'], $prefix);
		}
		// $sql .= self::linkByTagGroupBy($vars['includes']);
		// $sql .= self::linkByTagHavingCount($vars['includes']);
///print $sql;
		return $sql;
	}
	
	public static function tags($prefix){
		return "SELECT * FROM ".$prefix.self::tagsTable . ' ORDER BY name asc';		
	}
	
	public static function mapper($prefix){
		return 'SELECT map.id, map.linkid, map.tagid FROM '. $prefix.self::mapTable . ' map left join '.$prefix.self::tagsTable.' tags on map.tagid=tags.id order by tags.fulluri';		
	}
	

	/**
	 * @param string something like "music bla-music"/index.html.linklog
	 * @return string "music bla-music"
	 */
	public static function getQuerystringFromId($id){
		if (($pos = strrpos($id,"/")) > 0) {
			return substr($id,0,$pos);
		}
	}
	
	
	/// internal helper functions:
	
	private static function linkByTagGroupBy(){
		return ' GROUP BY links.id '; 
	}
	
	private static function linkByTagHavingCount($includes){
		return 'HAVING COUNT( linkid ) = ' . count($includes) . ' ';
	}

    private static function archiveBaseQuery($id, $prefix){
		$where = current(explode("/",str_replace('archive/', '', mysql_escape_string($id))));        
		$sql = 'FROM '.$prefix.self::linksTable.' links '.
		        'RIGHT JOIN '.$prefix.self::mapTable.' map ' .
		        'ON links.id=map.linkid
                LEFT JOIN '.$prefix.self::tagsTable.' tags ON ' .
		        'map.tagid=tags.id WHERE links.time LIKE "'.$where.'%" ';
        return $sql;
    }
	
	
	
	/*
	 * @param Array Tags to be included
	 */
	private static function getWhereIncludesTags($includes){
		return 'AND tags.fulluri IN ("'.implode('", "', $includes) .'")'. "\n" ;
	}

	/*
	 *
	 * */
	private static function linkByTagsExcludes($excludes, $prefix){
		return  'AND links.id NOT IN (SELECT links.id FROM '.$prefix.self::linksTable.' links, '.$prefix.self::mapTable.' map, '.$prefix.self::tagsTable.' tags WHERE links.id = map.linkid AND map.tagid = tags.id AND tags.fulluri  in  ("'.implode('", "', $excludes) .'"))';
	}

	private static function getBasicLinkQueryOrderBy(){
		return  'ORDER BY links.time DESC';
	}

    private static function getBasicLinkQueryCount($prefix){
        $sql = 	'SELECT count( distinct links.id) as counter '. self::getBasicLinkQueryMap($prefix);
		return $sql;  
    }

	private static function getBasicLinkQuery($prefix){
		$sql = 	'SELECT links.*,  ' . 
				'DATE_FORMAT(links.time, ' . '"%Y-%m-%dT%H:%i:%SZ") as isotime '. 
				self::getBasicLinkQueryMap($prefix);
		return $sql;
	}	


    private static function getBasicLinkQueryMap($prefix){
		$sql = 'FROM '.$prefix.self::linksTable . ' links,  '.
		        $prefix.self::mapTable . ' map,  '.
		        $prefix.self::tagsTable . ' tags '. 
		        'WHERE links.id=map.linkid AND map.tagid = tags.id ';
        return $sql;
    }

	
	
	/*
	 * @param string e.g. "include+include2-exclude-exclude2"
	 * @return array array('includes' => $includes, 'excludes' => $excludes);
	 */
	private static function splitQuerystringToParams($query){
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
	
	
}
