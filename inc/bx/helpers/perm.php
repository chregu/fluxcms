<?php

class bx_helpers_perm {
    static $accessHash = null;
    
    static function getUsername() {
        $perm = bx_permm::getInstance();
        return $perm->getUsername();
    }
    
    static function getUserId() {
        $perm = bx_permm::getInstance();
        return $perm->getUserId();
    }
    
    static function getUserGid() {
        $perm = bx_permm::getInstance();
        return $perm->getUserGid();
    }
    
    static function isAdmin() {
        $perm = bx_permm::getInstance();
        return $perm->isAllowed('/',array('admin'));
    }
    
    static function getAccessHash() {
        
        if (!self::$accessHash) {
            $db = $GLOBALS['POOL']->db;
            $px = $GLOBALS['POOL']->config->getTablePrefix();
        
            $query = "select value from ".$px."options where name='accesshash'";
            $res = $db->queryOne($query);
	    if(MDB2::isError($res)){
                 throw new PopoonDBException($res);
            }  
            self::$accessHash = $res; 
            
            if (!self::$accessHash) {
                $query = "delete from ".$px."options where name = 'accesshash'";
                $res = $GLOBALS['POOL']->dbwrite->query($query);
		if(MDB2::isError($res)){
                     throw new PopoonDBException($res);
                }
                
                $id = $db->nextId($px."_sequences");
                
                
                $h = md5(time() . rand(0,1000000000) . $GLOBALS['POOL']->config->magicKey.$id);
                
                $query = "insert into ".$px."options (id,name,value) values($id,'accesshash','$h')";
                self::$accessHash = $h;
                $res =  $GLOBALS['POOL']->dbwrite->query($query);
                if(MDB2::isError($res)){
                     throw new PopoonDBException($res);
                }
            }
        }
        return self::$accessHash;
        
    }
    static function updateAccessHash() {
        
            $db = $GLOBALS['POOL']->db;
            $px = $GLOBALS['POOL']->config->getTablePrefix();
        
            $id = $db->nextId($px."_sequences");
            $h = md5(time() . rand(0,1000000000) . $GLOBALS['POOL']->config->magicKey.$id);
                
            $query = "update ".$px."options set value ='$h' where name = 'accesshash'";
            $res = $GLOBALS['POOL']->dbwrite->query($query);
            if(MDB2::isError($res)){
                 throw new PopoonDBException($res);
            }
            
        return $h;
        
    }
	
	static function getAuthServices() {
		$authservices = array();
		$authservices['0'] = "OpenID";
		$authservices['1'] = "Flicker";
		return $authservices;
	}
	
	static function getPermGroups() {
		$db = $GLOBALS['POOL']->db;
		$px = $GLOBALS['POOL']->config->getTablePrefix();
		
		$query = "select * from ".$px."groups";
		
		$res = $GLOBALS['POOL']->db->query($query);
                if(MDB2::isError($res)){
                    throw new PopoonDBException($res);
                }	
		$group = array();
		$x = 0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$group[$x] = $row['id'] . "-" . $row['name'];
			$x++;
		}
		return $group;
	}
        
}

?>
