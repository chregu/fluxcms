<?php

class bx_metaindex {
    
    public static function setProperties($path,$values) {
        return bx_resourcemanager::setProperties($path,$values);
    }  
    
    
    public static function callIndexerFromFilename($fspath,$id = null) {
        
        $mt = popoon_helpers_mimetypes::getFromFileLocation($fspath);
        $mt = "bx_indexer_".str_replace("/","_",str_replace(array(".","-"),"",$mt));
        if (class_exists($mt)) {
            $indexer = new $mt;
            $props = $indexer->getMetadataForFile($fspath);
            if ($id ) {
                self::setProperties($id,$props);
            }   
            return $props;
            
        }
        return null;
    }
    
    public static function splitTags($tags) {
        if (strpos($tags,",")) {
            $tag = split(" *, *",str_replace('"',"",$tags));
        } else {
            $tag = array();
            if (preg_match_all('#"([^"]*)"#',$tags,$matches)) {
                foreach($matches[0] as $match) {
                    $tag[] = str_replace('"','',$match);
                    $tags =   str_replace($match,"",$tags);
                }
            }
            $tag = array_merge($tag,split(" +",trim($tags)));
        }
        return $tag;
    }
    
    static public function implodeTags($tags) {
        $t = '';
        foreach ($tags as $tag) {
            if (strpos($tag,' ')) {
                $t  .= '"'.$tag.'" ';
            } else {
                $t .= $tag .' ';   
            }
        }
        return $t;
    }

    
    public static function setTags($id, $tags, $doProperty = false) {
        $tags = bx_helpers_string::utf2entities(trim($tags));
        if ($doProperty) {
            bx_resourcemanager::setProperty($id,'subject',$tags, 'http://purl.org/dc/elements/1.1/');
        } 
         $db = $GLOBALS['POOL']->db;
         
        $t = array();
        $tags = self::splitTags($tags);
        foreach($tags as $tag) {
            $t[] = $db->quote($tag);
        }
        $tagsImpl = implode(",",$t);
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
       
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        $query = "select tag from ".$tablePrefix."tags  where tag in (".$tagsImpl. ")";
        $res = $db->query($query);
        $ids = $res->fetchCol();
        // insert new tags
        foreach ($tags as $value) {
            if (!(in_array($value,$ids))) {
                $seqid = $dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
                $query = "insert into ".$tablePrefix."tags (id, tag) VALUES ($seqid, ".$db->quote($value).")";
                $ids[] = $seqid;
                $res = $dbwrite->query($query);
            }
        }
        
        $query = "select id from ".$tablePrefix."tags  where tag in (".bx_helpers_string::utf2entities($tagsImpl).")";
        $res = $db->query($query);
        $ids = $res->fetchCol();
        
        if (count($ids) > 0) {
            $query = "delete from ".$tablePrefix."properties2tags where path = '".$id."' and not( tag_id in (".implode(",",$ids)."))";
            $res = $dbwrite->query($query);
            
            //get old tags
            
            $query = "select tag_id from ".$tablePrefix."properties2tags where path = '".$id."' and ( tag_id in (".implode(",",$ids)."))";
            $res = $dbwrite->query($query);
            $oldids = $res->fetchCol();
        } else {
            //delete all
            $query = "delete from ".$tablePrefix."properties2tags where path = '".$id."' ";
            $res = $dbwrite->query($query);
            $oldids = array();
        }
        
        
        // add new relations
        foreach ($ids as $value) {
            if (!(in_array($value,$oldids))) {
                $seqid = $dbwrite->nextID($GLOBALS['POOL']->config->getTablePrefix()."_sequences");
                $query = "insert into ".$tablePrefix."properties2tags (id, path, tag_id) VALUES ($seqid, ".$db->quote($id).", $value)";
                $res = $dbwrite->query($query);
            }
        }
    }
    
    public static function removeAllTagsById($id, $doProperty = false) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
        $query = "delete from ".$tablePrefix."properties2tags where path = '".$id."' ";
        $db->query($query);
        if ($doProperty) {
            bx_resourcemanager::removeProperty($id,'subject', 'http://purl.org/dc/elements/1.1/');
        }
        
    }
    
    public static function getRelatedInfoByTags($tags, $excludePath = null, $pathRestriction = null) {
        $relatedIds = self::getRelatedIdsByTags($tags,$excludePath,$pathRestriction);
        return self::getRelatedInfoByIds($relatedIds);
    }
    
   
    
    public static function getRelatedInfoBySearch($search, $options = array()) {
        /*$options = array(
        'excludePath' => null,
        'pathRestrictions' => null,
        'searchStart' => null,
        'searchNumber' => null
        );*/
        
        $relatedIds = self::getRelatedIdsBySearch($search,$options);
        return self::getRelatedInfoByIds($relatedIds);
        
    }
    
    /*
    public static function getRelatedInfoBySearch($search, $excludePath = null, $pathRestriction = null) {
        $relatedIds = self::getRelatedIdsBySearch($search,$excludePath,$pathRestriction);
        return self::getRelatedInfoByIds($relatedIds);
    }
    */
    public static function getRelatedInfoByIds($relatedIds) {
        foreach ($relatedIds as $id => $value) {
            
            $parts = bx_collections::getCollectionAndFileParts($id);
            $res = $parts['coll']->getPluginResourceById($parts['rawname']);
            
            if ($res) {
                $relatedIds[$id]['title'] = $res->getTitle();
                $relatedIds[$id]['outputUri'] = $res->getOutputUri();
                $relatedIds[$id]['resourceDescription'] = $res->getResourceDescription();
                $relatedIds[$id]['lastModified'] = $res->getLastModified();
                $relatedIds[$id]['status'] = $res->getStatus();
            } else {
                unset($relatedIds[$id]);
            }
        }
        usort($relatedIds, array("bx_metaindex","relatedInfoSort"));
        return $relatedIds;
    }
    
    public static function relatedInfoSort ($a,$b) {
        if ($a['cnt'] > $b['cnt']) {
            return -1;
        } elseif ($a['cnt'] < $b['cnt']) {
            return 1;
        }
        
        if ($a['lastModified'] > $b['lastModified']) {
            return -1;
        } else if ($a['lastModified'] < $b['lastModified']) {
            return 1;
        } 
        return 0;
    }
    
    public static function getRelatedIdsBySearch($search, $options) {
        
        $pathRestriction = $options['pathRestrictions'];
        $excludePath = $options['excludePath'];
        
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        $query = "select  properties.path, sum(MATCH (value) AGAINST (". $db->quote($search) .")) as cnt
        from ".$tablePrefix."properties as properties  where 
        MATCH (value) AGAINST (" . $db->quote($search) .") ";
        if ($excludePath) {
            $query .= " and properties.path != ".$db->quote($excludePath) ." ";
        }
        if ($pathRestriction) {
            $query .= " and (properties.path like ".$db->quote($pathRestriction) .") ";
        }
        $query .= "group by properties.path order by cnt DESC LIMIT ".$options['searchStart'].",".$options['searchNumber'];
        $res = $db->query($query);
        
        $ids = $res->fetchAll(MDB2_FETCHMODE_ASSOC,"path",true);
        return $ids;
    }
    
    public static function getRelatedIdsByTags($tags, $excludePath = null, $pathRestriction = null) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
      
        $query = "select  properties.path,count(*) as cnt 
                     from ".$tablePrefix."properties as properties  
                    join ".$tablePrefix."properties2tags as properties2tags on 
                        properties.path = properties2tags.path   
                    join ".$tablePrefix."tags as tags on 
                        tag_id = tags.id 
                     where ";
	$query .= '(';
	foreach ($tags as $tag) {
		$query .= 'tags.tag = '.$db->quote($tag) .' or ';
	}
	$query .= ' 1=0)';	

        if ($excludePath) {
            $query .= " and properties.path != ".$db->quote($excludePath) ." ";
        }
        if ($pathRestriction) {
            $query .= " and (properties.path like ".$db->quote($pathRestriction) .") ";
        }
        
        $query .= "group by properties.path order by cnt DESC";
        $res = $db->query($query);
        $ids = $res->fetchAll(MDB2_FETCHMODE_ASSOC,"path",true);
        return $ids; 
    }
    
    public static function getRelatedTitlesByTags($tags, $excludePath = null) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $ids = self::getRelatedIdsByTags($tags,$excludePath);
        if (count($ids) > 0) {
            $query = "select path,value as title from ".$tablePrefix."properties where ( 1 = 0 ";
            foreach ($ids as $key => $value) {
                $query .= " or path = '".$key. "' ";
            }
            
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            $query .= ") and name = 'title' and (ns = 'bx:' or ns = 'bx:".$lang."')";
           
            $db = $GLOBALS['POOL']->db;
            $res = $db->query($query);
            
            return array_merge_recursive($ids,$res->fetchAll(MDB2_FETCHMODE_ASSOC,"path",true));
        } else {
            return array();
        }
        
    }
    
    public static function getTagsById ($id, $asArray = true) {
        $tags = trim(bx_resourcemanager::getProperty($id,'subject','http://purl.org/dc/elements/1.1/'));
        if ($tags) {
            if ($asArray) {
                return self::splitTags($tags);
            } else {
                return $tags;
            }
            
        } else {
            if ($asArray) {
                return array();
            } else {
                return "";
            }
        }
    }
    
     public static function getTagsByIds ($ids) {
        $_tags = bx_resourcemanager::getPropertyMultiple($ids,'subject','http://purl.org/dc/elements/1.1/');
        $tags = array();
        foreach ($_tags as $key => $value) {
            $tags[$key] = self::splitTags($value);
        }
        return $tags;
        
    }
}
