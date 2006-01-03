<?php
/**
* the resource manager
*
* A central place for asking properties of resources.
* mimetype etc..
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  bxcms
*/

class bx_resourcemanager {

    
    /**
    * constructor.
    * should not be called from public
    * this is a static class right now
    */
    private function __construct() {


    }

    /**
    * gets the resource type (mimetype) of an $uri
    *
    * currently, it just looks at the file ending...
    *
    * @param string $url the full $url to the resource
    * @return string the resource type
    *
    */
    static public function getMimeType($id) {
        // FIXME: LIKE is ugly...
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select value from ".$prefix."properties where path = '$id' and name = 'mimetype'";
        $res =  $GLOBALS['POOL']->db->query($query);
        $mimetype = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        if ($mimetype) {
            return $mimetype[0];
        } else {
            return NULL;
        }
    }
    
    static public function getResourceId($dir, $filename, $ext = "") {
        $query = "select path from ".$GLOBALS['POOL']->config->getTablePrefix()."properties where path like '$dir$filename%'  LIMIT 1";
        $res =  $GLOBALS['POOL']->db->query($query);
        if ($GLOBALS['POOL']->db->isError($res)) {
            throw new PopoonDBException($res);
        }
        $path = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        if ($path) {
            return $path[0];
        } else {
            return NULL;
        }
        
    }
    static public function getChildrenByMimeType(bx_collection $coll, $mimetype) {
        $children = array();
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select p1.path as path, p2.value as mimetype from ".$prefix."properties as p1 left join ".$prefix."properties as p2 on p1.path = p2.path where p1.name = 'parent-uri' and p1.value = '".$coll->uri."' and p2.name='mimetype' and p2.value = '$mimetype'";
        
        $res =  $GLOBALS['POOL']->db->query($query);
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $uri =  $row['path'];
            /*check if uri is child of $coll->uri */
            if( strpos($uri,$coll->uri) === 0 && $coll->uri != $uri) {
                $children[] = $uri;
            }
        }
        return $children;
    }
    
    
    public static function getAllProperties ($path ,$namespace =  NULL) {
         $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select ns as namespace, value, name from ".$prefix."properties where path = ".$GLOBALS['POOL']->db->quote($path)." ";
        if ($namespace) {
            $query .= " and ns = '$namespace'";
        }
        $res = $GLOBALS['POOL']->db->query($query);
        if ($GLOBALS['POOL']->db->isError($res)) {
            throw new PopoonDBException($res);
        }

        $props = array();
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $props[$row['namespace'].":".$row['name']] = $row;
        }
        //FIXME hardcoded admin xslt names... not nice ;)
     /*   if ($namespace == BX_PROPERTY_PIPELINE_NAMESPACE && strpos($path,"/admin/") === 0 ) {
                $props[$namespace.":xslt"] = array (
                                                "namespace" => BX_PROPERTY_PIPELINE_NAMESPACE,
                                                "name" => "xslt",
                                                "value" => substr($path,7,-1).".xsl"
                                                );
        }*/
        return $props;
        
    }

    public static function getPropertyMultiple($paths, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $paths = "('".implode("','",$paths)."')";
        $query = "select path, value from ".$prefix."properties where path in $paths and name = '$name' and ns = '$namespace'"; 
        $res = $GLOBALS['POOL']->db->query($query);
        if ($GLOBALS['POOL']->db->isError($res)) {
            throw new PopoonDBException($res);
        }
        return $res->fetchAll(MDB2_FETCHMODE_ASSOC,"path");
        
    }
    
    
    public static function getProperty($path, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select value from ".$prefix."properties where path = '$path' and name = '$name' and ns = '$namespace'"; 
        $res = $GLOBALS['POOL']->db->query($query);
        if ($GLOBALS['POOL']->db->isError($res)) {
            throw new PopoonDBException($res);
        }
        
        $row = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        if ($row) {
            return $row[0];
        } else {
            return NULL;
        }
    }
    
    public static function getFirstProperty($path, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $val = self::getProperty($path,$name,$namespace);
        if ($val) {
            return $val;
        }
        if ($path == '/') {
            return null;
        }
        
        $path = dirname($path);
        while ($path) {
         if ($path == '/') {
               $path = '';
         }
          $val = self::getProperty($path.'/',$name,$namespace);
          if ($val) {
              return $val;
          } 
          if ($path == '') {
              return NULL;
          }
          $path = dirname($path);
        }
        return NULL;
    }

	public static function getFirstPropertyAndPath($path, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $val = self::getProperty($path,$name,$namespace);
        if ($val) {
            return array('property' => $val, 'path' => $path);
        }
        if ($path == '/') {
            return null;
        }
        
        $path = dirname($path);
        while ($path) {
          $val = self::getProperty($path.'/',$name,$namespace);
          if ($val) {
              return array('property' => $val, 'path' => $path);
          } 
          if ($path == '/') {
              return NULL;
          }
          $path = dirname($path);
        }
        return NULL;
    }
	    
    public static function setProperty($path, $name, $value = NULL, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        //clean path
        $path = preg_replace("#\/{2,}#","/",$path);
        $query = "DELETE FROM ".$prefix."properties WHERE path = '$path' AND name = '$name' AND ns = '$namespace'";
        $db = $GLOBALS['POOL']->dbwrite;
        $db->query($query);
        if(isset($value)) {
            $query = 'INSERT INTO '.$prefix.'properties  (path,name,ns,value) VALUES (';
            $query .= $db->quote($path) .',';
            $query .= $db->quote($name) .',';
            $query .= $db->quote($namespace) .',';
            $query .= $db->quote($value) .')';
            if ($db->isError($res = $db->query($query),true)) {
                bx_log::log("MDB2 error:". $res->getMessage() . $res->getUserInfo(), PEAR_LOG_ERR);
                return false;
            }
        }
        return true;
    }
    
    public static function setProperties($path,$values) {
      foreach($values as $ns => $vals) {
          foreach($vals as $name => $value) {
                self::setProperty($path,$name, $value,$ns);   
          }
      }
    }

    public static function removeProperty($path, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$prefix."properties where path = '$path' and name = '$name' and ns = '$namespace'"; 
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        return TRUE;
    }
    
    public static function removeAllProperties ($path ,$namespace =  NULL) {
         $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$prefix."properties where path = '$path' ";
        
        if ($namespace) {
            $query .= " and ns = '$namespace'";
        }
        
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        if ($GLOBALS['POOL']->dbwrite->isError($res)) {
            return FALSE;
        }
        
        return TRUE;
    }

}

?>
