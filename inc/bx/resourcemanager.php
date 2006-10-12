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

    
    private static $getPropertyStm = null;
    private static $getAllPropertiesStm = null;
    private static $getAllPropertiesStmNs = null;
    private static $props = array();
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
        return self::getProperty($id,'mimetype');
    }
    
    static public function getResourceId($dir, $filename) {
        
        $key = "res_".$dir .":". $filename;
        if (($val = $GLOBALS['POOL']->cache->get($key)) !== false) {
            return $val;
        }
        $query = "select path from ".$GLOBALS['POOL']->config->getTablePrefix()."properties where path like '$dir$filename%'  LIMIT 1";
        $res =  $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        $path = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        if ($path) {
            $val = $path[0];
            $GLOBALS['POOL']->cache->set($key,$val);
        } else {
            $val = NULL;
        }
        return $val;
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
        $key = "res_".$path .":". $namespace;
        if (($val = $GLOBALS['POOL']->cache->get($key)) !== false) {
            return $val;
        }
        if ($namespace) {
            if (!self::$getAllPropertiesStmNs) {
                $prefix = $GLOBALS['POOL']->config->getTablePrefix();
                $query = "select ns as namespace, value, name from ".$prefix."properties where path = :path and ns = :ns";
                self::$getAllPropertiesStmNs = $GLOBALS['POOL']->db->prepare($query,array('text','text'));
            }
            $res = self::$getAllPropertiesStmNs->execute(array('path' => $path,'ns'=>$namespace));
        } else {
            if (!self::$getAllPropertiesStm) {
                $prefix = $GLOBALS['POOL']->config->getTablePrefix();
                $query = "select ns as namespace, value, name from ".$prefix."properties where path = :path";
                self::$getAllPropertiesStm = $GLOBALS['POOL']->db->prepare($query,array('text'));
            }
            $res = self::$getAllPropertiesStm->execute(array('path' => $path));
        }
        
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }

        $props = array();
        
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $props[$row['namespace'].":".$row['name']] = $row;
            
        }
        $GLOBALS['POOL']->cache->set($key,$props);
        return $props;
        
    }

    public static function getPropertyMultiple($paths, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $paths = "('".implode("','",$paths)."')";
        $query = "select path, value from ".$prefix."properties where path in $paths and name = '$name' and ns = '$namespace'"; 
        $res = $GLOBALS['POOL']->db->query($query);
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        return $res->fetchAll(MDB2_FETCHMODE_ASSOC,"path");
        
    }
    
    
    public static function getProperty($path, $name, $namespace = BX_PROPERTY_DEFAULT_NAMESPACE) {
        
        $key = "res_".$path .":". $name .":".$namespace;
        if (($val = $GLOBALS['POOL']->cache->get($key)) !== false) {
            return $val;
        }
        if (!self::$getPropertyStm) {
            $prefix = $GLOBALS['POOL']->config->getTablePrefix();
            $query = "select value from ".$prefix."properties where path = :path and name = :name and ns = :namespace"; 
            self::$getPropertyStm = $GLOBALS['POOL']->db->prepare($query,array('text','text','text'),array('text'));
        }
        $res = self::$getPropertyStm->execute(array('path' => $path, 'name' => $name, 'namespace' => $namespace));
        if (MDB2::isError($res)) {
            throw new PopoonDBException($res);
        }
        
        $row = $res->fetchRow(MDB2_FETCHMODE_ORDERED);
        if ($row) {
            $val =  $row[0];
            $GLOBALS['POOL']->cache->set($key,$val);
        } else {
            $val = NULL;
        }
        
        return $val;
        
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
        $GLOBALS['POOL']->cache->del("res_".$path.":".$name.":".$namespace);
        $GLOBALS['POOL']->cache->del("res_".$path.":".$namespace);
        $GLOBALS['POOL']->cache->del("res_".$path.":");
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        //clean path
        $path = preg_replace("#\/{2,}#","/",$path);
        
        $query = "DELETE FROM ".$prefix."properties WHERE path = :path AND name = :name AND ns = :namespace";
        $db = $GLOBALS['POOL']->dbwrite;
        $stm = $db->prepare($query);
        $stm->execute(array('path' => $path,'name' => $name,'namespace' => $namespace));
        if(isset($value)) {
            $query = 'INSERT INTO '.$prefix.'properties  (path,name,ns,value) VALUES (:path,:name,:namespace,:value)';
            $stm = $db->prepare($query);
            $stm->execute(array('path' => $path,'name' => $name,'namespace' => $namespace,'value' => $value));
            if (MDB2::isError($res = $stm->execute(array('path' => $path,'name' => $name,'namespace' => $namespace,'value' => $value)),true)) {
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
        $GLOBALS['POOL']->cache->del("res_".$path.":".$name.":".$namespace);
        $GLOBALS['POOL']->cache->del("res_".$path.":".$namespace);
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$prefix."properties where path = '$path' and name = '$name' and ns = '$namespace'"; 
        $res = $GLOBALS['POOL']->dbwrite->query($query);
        return TRUE;
    }
    
    public static function removeAllProperties ($path ,$namespace =  NULL) {
        $GLOBALS['POOL']->cache->del("res_".$path.":".$namespace);
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "from ".$prefix."properties where path = '$path' ";
        
        if ($namespace) {
            $query .= " and ns = '$namespace'";
        }
        $res = $GLOBALS['POOL']->dbwrite->query("select * ".$query);
        while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
              $GLOBALS['POOL']->cache->del("res_".$path.":".$row['name'].":".$row['ns']);
        }
        
        $res = $GLOBALS['POOL']->dbwrite->query("delete ".$query);
        if (MDB2::isError($res)) {
            return FALSE;
        }
        
        return TRUE;
    }

}

?>
