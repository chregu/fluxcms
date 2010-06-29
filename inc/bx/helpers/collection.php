<?php 
class bx_helpers_collection {
    
    public static function getLastModified($collectionUri, $mode="output") {
        $parts = bx_collections::getCollectionAndFileParts($collectionUri, $mode);
        $collection = $parts['coll'];
        if ($collection instanceof bx_collection) {
            $lastModified = $collection->getLastModifiedResource();
            return $lastModified;        
        }
        return "";   
    }
    
    public static function getLastModifiedGM($collectionUri, $mode="output") {
        $lastModTs = self::getLastModified($collectionUri, $mode);
        if ($lastModTs) {
            return gmdate('D, d M Y H:i:s T',$lastModTs);
        }
        return "";
    }
    
    public static function getLastModifiedFormatted($collectionUri, $mode="output", $format="Y-m-d H:i:s") {
        $lastModTs = self::getLastModified($collectionUri, $mode);
        if ($lastModTs) {
            return date($format,$lastModTs);
        }
        return "";
    }
    
}
?>