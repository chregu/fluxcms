<?php
class bx_plugins_admin_users extends bx_plugins_admin implements bxIplugin  {
    
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!bx_plugins_admin_users::$instance) {
            bx_plugins_admin_users::$instance = new bx_plugins_admin_users($mode);
        } 
        
        return bx_plugins_admin_users::$instance;
    }
    

   /* protected function getFullPath($path, $name, $ext) {
        return $path.$name;
    }*/
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if ($ext) {
            return $name.".$ext";    
        } else if ($name == '') {
            return '/';
        } else {
            return $name;
        }
        
    }
    
    public function getContentById($path, $id) {
		$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		
		$xml = $this->getUsers($id, $tablePrefix);
		$dom = new domDocument();
		$dom->loadXML($xml);
		
		return $dom;
    }
    
    protected function getUsers($id, $tablePrefix) {
		$groups = bx_helpers_perm::getPermGroups();
		$services = bx_helpers_perm::getAuthServices();
		
		
		$xml = "<useradministration>";
		$xml .= "<authservices>";
		
		foreach($services as $service) {
			$xml .= "<authservice>";
			$xml .= "<authservice-name>".$service."</authservice-name>";
			$xml .= "</authservice>";
		}
		$xml .= "</authservices>";
		
		$xml .= "<groups>";
		
		foreach($groups as $group) {
			$matches = explode('-',$group);
			$xml .= "<group>";
			$xml .= "<group-id>".$matches['0']."</group-id>";
			$xml .= "<group-name>".$matches['1']."</group-name>";
			$xml .= "</group>";
		}
		$xml .= "</groups>";
		
		//removes a users
		if(isset($_GET['delete']) && $_GET['delete']) {
			$user_del = $_GET['delete'];
		}
		
		if(isset($user_del)) {
			$query = "delete from ".$tablePrefix."users where id = ".$user_del;
			$GLOBALS['POOL']->db->query($query);
		}
		
		if(isset($_GET['id']) && $_GET['id']) {
			$user_id = $_GET['id'];
		}
		
		if(isset($_GET['add']) && $_GET['add']) {
			$user_add = $_GET['add'];
		}
		
		$i18n = $GLOBALS['POOL']->i18nadmin;
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		
		if($id == "/edit/" and isset($user_id)) {
			
			$query = "select * from ".$tablePrefix."users where ID =".$user_id;
				
			$res = $GLOBALS['POOL']->db->query($query);
			
			
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			
			$xml .= "<user>";
			$xml .= "<username>".$row['user_login']."</username>";
			$xml .= "<fullname>".$row['user_fullname']."</fullname>";
			$xml .= "<mail>".$row['user_email']."</mail>";
			$xml .= "<id>".$row['id']."</id>";
			$xml .= "<user_gupi>".$row['user_gupi']."</user_gupi>";
			$xml .= "<user_adminlang>".$row['user_adminlang']."</user_adminlang>";
			$xml .= "<plazes_username>".$row['plazes_username']."</plazes_username>";
			$xml .= "<plazes_password>".$row['plazes_password']."</plazes_password>";
			
			//reads services fromd atabase
			$query_authservices = "select * from ".$tablePrefix."userauthservices where user_id = '".$user_id."'";
			
			$res_services = $GLOBALS['POOL']->db->query($query_authservices);
			
			$xml .= "<services>";
			while($row = $res_services->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$xml .= "<service>";
				$xml .= "<id>".$row['id']."</id>";
				$xml .= "<user_id>".$row['user_id']."</user_id>";
				$xml .= "<servicename>".$row['service']."</servicename>";
				$xml .= "<account>".$row['account']."</account>";
				$xml .= "</service>";
			}
			$xml .= "</services>";
			
			
			$query_groups = "select fk_group from ".$tablePrefix."groups left join ".$tablePrefix."users2groups on ".$tablePrefix."groups.id = ".$tablePrefix."users2groups.fk_group where ".$tablePrefix."users2groups.fk_user = '".$user_id."'";
			$res_groups = $GLOBALS['POOL']->db->query($query_groups);
			
			$xml .= "<groups>";
			while($row = $res_groups->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				$xml .= "<group>";
				$xml .= "<id>".$row['fk_group']."</id>";
				$xml .= "</group>";
			}
			$xml .= "</groups>";
			
			
			$xml .= "</user>";
			
		} else {
			if($id == "/edit/" and isset($user_add)) {
				$xml .= "<new/>";
			} else {
				$query = "select user_login, user_fullname, user_email, ID from ".$tablePrefix."users";
				
				$res = $GLOBALS['POOL']->db->query($query);
				
				$xml .= "<users>";
				
				while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$xml .= "<user>";
					$xml .= "<username>".$row['user_login']."</username>";
					$xml .= "<fullname>".$row['user_fullname']."</fullname>";
					$xml .= "<mail>".$row['user_email']."</mail>";
					$xml .= "<id>".$row['id']."</id>";
					$xml .= "</user>";
				}
				
				$xml .= "</users>";
			}
		}
		$xml .= "</useradministration>";
		return $xml;
	}
    
    /* FIXME:: this should be cleaned up. arguments are $path,$id,$data,$mode */
    public function handlePost($path, $name, $ext, $data=null) {
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		$perm = bx_permm::getInstance();
		$active_user_id = $_SESSION['_authsession']['data']['id'];
		$services = bx_helpers_perm::getAuthServices();
		$groups = bx_helpers_perm::getPermGroups();
		
		
		if (!$perm->isAllowed('/permissions/',array('permissions-back-users'))) {
    		throw new BxPageNotAllowedException();
    	}
        
        if ($data == NULL) {
            $data = $_REQUEST['bx']['plugins']['admin_users'];
            $data = bx_helpers_globals::stripMagicQuotes($data);
        }
		if(isset($_GET['id']) && $_GET['id']) {
			$user_id = $_GET['id'];
		}
		
		$pwd_query = "select user_pass from ".$tablePrefix."users where id = '".$active_user_id."'";
		$res = $GLOBALS['POOL']->db->query($pwd_query);
		$re = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
		if($re['user_pass'] == md5($data['pwd'])) {
			if(isset($user_id)) {
				$this->updateUserData($data, $services, $groups, $user_id);
			} else {
				$this->insertUserData($data, $services, $groups);
			}
		} else {
			print "<p style='color:red'>You had a misstake in your password</p>";
		}
	}
    protected function getParentCollection($path) {
        $parent = dirname($path);
        if ($parent != "/"){
            $parent .= '/';
        }
        
        return bx_collections::getCollection($parent,"output");
    }

    protected function getAction() {
        return !empty($_GET['action']) ? $_GET['action'] : FALSE;
    }
    
    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getDataUri($path, $name, $ext) {
        return FALSE;
    }

    public function getEditorsByRequest($path, $name, $ext) {
        return array();   
    }

		/** bx_plugin::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
				$params = array();
				$params['xslt'] = 'addresource.xsl';
				return $params;
		}
    
	protected function updateUserData($data, $services, $groups, $user_id) {
		$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		$perm = bx_permm::getInstance();
		$active_user_id = $_SESSION['_authsession']['data']['id'];
		
		if(isset($data['new_pwd']) && $data['new_pwd'] && isset($data['new_pwd_re']) && $data['new_pwd_re']) {
			if($data['new_pwd'] == $data['new_pwd_re']) {
				$query = "update ".$tablePrefix."users SET user_login = '".$data['username']."', user_fullname = '".$data['fullname']."', user_email = '".$data['mail']."', user_gupi = '".$data['gupi']."', user_adminlang = '".$data['lang']."', plazes_username = '".$data['plazes_username']."', plazes_password = '".$data['plazes_pwd']."' , user_pass = '".md5($data['new_pwd'])."' where id = ".$user_id;
			} else {
				print "<p style='color:red'>New password didn't match try again please</p>";
			}
		}
		
		//update  services
		foreach($services as $service) {
			if(isset($data[$service]) && $data[$service]) {
				$userauthservices_query = "update ".$tablePrefix."userauthservices set service = '".$service."', account = '".$data[$service]."' where user_id = '".$user_id."' and service = '".$service."'";
				$res = $GLOBALS['POOL']->db->query($userauthservices_query);
			}
		}
		$usergroupsdel_query = "delete from ".$tablePrefix."users2groups where fk_user = '".$user_id."'";
		$res = $GLOBALS['POOL']->db->query($usergroupsdel_query);
		//update  groups
		foreach($groups as $group) {
			$matches = explode('-',$group);
		if(isset($data[$matches['1']]) && $data[$matches['1']]) {
				//$usergroups_query = "update ".$tablePrefix."user2groups set  fk_group = '".$matches['0']."', fk_user = '".$data[$group]."' where user_id = '".$user_id."' and service = '".$service."'";
				$usergroups_query = "insert into ".$tablePrefix."users2groups (fk_user , fk_group) values('".$user_id."' , '".$matches['0']."')"; // on dublicate key update fk_user = fk_user and fk_group = fk_group";
				$res = $GLOBALS['POOL']->db->query($usergroups_query);
				
			}
		}
		if(!isset($query)) {
			$query = "update ".$tablePrefix."users SET user_login = '".$data['username']."', user_fullname = '".$data['fullname']."', user_email = '".$data['mail']."'";
			if(isset($data['gupi']) && $data['gupi']) {
				$query .= ", user_gupi = '".$data['gupi']."'";
			}
			$query .= ", user_adminlang = '".$data['lang']."', plazes_username = '".$data['plazes_username']."', plazes_password = '".$data['plazes_pwd']."' , user_pass = '".$data['new_pwd']."' where id = ".$user_id;		
		}
		if(isset($query)) {
			$res = $GLOBALS['POOL']->db->query($query);
		}
	}
	
	protected function insertUserData($data, $services, $groups) {
		$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		$perm = bx_permm::getInstance();
		$active_user_id = $_SESSION['_authsession']['data']['id'];
		
		if(isset($data['add']) && $data['add']) {
			$query = "insert into ".$tablePrefix."users (user_login, user_fullname, user_email, user_gupi, user_adminlang, plazes_username, plazes_password, user_pass) value('".$data['username']."', '".$data['fullname']."', '".$data['mail']."', '".$data['gupi']."', '".$data['lang']."', '".$data['plazes_username']."', '".$data['plazes_pwd']."', '".md5($data['new_pwd'])."')";
			
			if(isset($query)) {
				$res = $GLOBALS['POOL']->db->query($query);
			}
			
			//insert services
			foreach($services as $service) {
				if(isset($data[$service]) && $data[$service]) {
					$max_id_query = "select MAX(id) as last_id from ".$tablePrefix."users";
					$result = $GLOBALS['POOL']->db->query($max_id_query);
					$insert_id = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
					$userauthservices_query = "insert into ".$tablePrefix."userauthservices (user_id, service, account) value('".$insert_id['last_id']."', '".$service."', '".$data[$service]."')";
					$res = $GLOBALS['POOL']->db->query($userauthservices_query);
				}
			}
			
			
			//insert groups
			foreach($groups as $group) {
				$matches = explode('-',$group);
				if(isset($data[$matches['1']]) && $data[$matches['1']]) {
					$max_id_query = "select MAX(id) as last_id from ".$tablePrefix."users";
					$result = $GLOBALS['POOL']->db->query($max_id_query);
					$insert_id = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
					$userauthgroups_query = "insert into ".$tablePrefix."users2groups (fk_user, fk_group) value('".$insert_id['last_id']."', '".$matches['0']."')";
					$res = $GLOBALS['POOL']->db->query($userauthgroups_query);
				}
			}
			
		}
		
	}
	
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
    public function getCommentCaptchaDays() {
        return $GLOBALS['POOL']->config->blogCaptchaAfterDays;
    }
    
}
?>
