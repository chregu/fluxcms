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
		$xml = $this->getUsers($id);
		return $xml;
    }
    
    protected function getUsers($id) {
		
		if(isset($_GET['id'])) {
			$user_id = $_GET['id'];
		}
		
		$i18n = $GLOBALS['POOL']->i18nadmin;
        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
		
		if($id == "/edit/" and isset($user_id)) {
			
			$query = "select * from ".$tablePrefix."users where ID =".$user_id;
			$res = $GLOBALS['POOL']->db->query($query);
			$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
			
			$xml = "<user>";
			$xml .= "<user>";
			$xml .= "<username>".$row['user_login']."</username>";
			$xml .= "<fullname>".$row['user_fullname']."</fullname>";
			$xml .= "<mail>".$row['user_email']."</mail>";
			$xml .= "<id>".$row['id']."</id>";
			$xml .= "<user_gupi>".$row['user_gupi']."</user_gupi>";
			$xml .= "<user_gid>".$row['user_gid']."</user_gid>";
			$xml .= "<user_adminlang>".$row['user_adminlang']."</user_adminlang>";
			$xml .= "<plazes_username>".$row['plazes_username']."</plazes_username>";
			$xml .= "<plazes_password>".$row['plazes_password']."</plazes_password>";
			$xml .= "</user>";
			$xml .= "</user>";
			
		} else {
			$query = "select user_login, user_fullname, user_email, ID from ".$tablePrefix."users";
			$res = $GLOBALS['POOL']->db->query($query);
			
			$xml = "<users>";
			
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
		$dom = new domDocument();
		$dom->loadXML($xml);
		return $dom;
	}
    
    /* FIXME:: this should be cleaned up. arguments are $path,$id,$data,$mode */
    public function handlePost($path, $name, $ext, $data=null) {
        $perm = bx_permm::getInstance();
		if (!$perm->isAllowed('/permissions/',array('permissions-back-users'))) {
    		throw new BxPageNotAllowedException();
    	}
        
        if ($data == NULL) {
            $data = $_REQUEST['bx']['plugins']['admin_users'];
            $data = bx_helpers_globals::stripMagicQuotes($data);
        }
        
		if(isset($_GET['id'])) {
			$user_id = $_GET['id'];
		}
		
		$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        //$query = "insert into ".$tablePrefix."users (user_login, user_fullname, user_email, user_gupi, user_gid, user_adminlang, plazes_username, plazes_password) VALUES('".$data['username']."','".$data['fullname']."','".$data['mail']."','".$data['gupi']."','".$data['gid']."','".$data['lang']."','".$data['plazes_username']."','".$data['plazes_pwd']."') where id = ".$user_id;
		$query = "update ".$tablePrefix."users SET user_login = '".$data['username']."', user_fullname = '".$data['fullname']."', user_email = '".$data['mail']."', user_gupi = '".$data['gupi']."', user_gid = '".$data['gid']."', user_adminlang = '".$data['lang']."', plazes_username = '".$data['plazes_username']."', plazes_password = '".$data['plazes_pwd']."' where id = ".$user_id;
		$res = $GLOBALS['POOL']->db->query($query);
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
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
    public function getCommentCaptchaDays() {
        return $GLOBALS['POOL']->config->blogCaptchaAfterDays;
    }
    
}
?>
