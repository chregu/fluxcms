<?php
class bx_plugins_admin_openid extends bx_plugins_admin implements bxIplugin  {
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_plugins_admin_openid($mode);
        } 
        
        return self::$instance;
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
    
    public function getContentById() {
        include_once("Auth/OpenID/Server.php");
        //throw new BxPageNotAllowedException();
        $server = bx_helpers_openid::getServer();
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        $conf = bx_config::getInstance();
        
        $confvars = $conf->getConfProperty('permm');
        $permObj = bx_permm::getInstance($confvars);
        
        //hier werden die user ∈der übersich editiert
        
        if(isset($_POST['UserEditForm'])) {
            $this->saveUserProfile($_POST, $tablePrefix);
        }
        
        if (!$permObj->isAllowed('/',array('admin')) &&  !(isset($_POST['openid_mode']) && ($_POST['openid_mode'] == 'associate' || $_POST['openid_mode'] == 'check_authentication'))) {
            if (isset($_GET["openid_mode"]) && $_GET["openid_mode"]== 'checkid_immediate') {
                $server = bx_helpers_openid::getServer();
                $answer = $server->getOpenIDResponse(false,"GET");
                if ($answer[0] == "redirect") {
                    header("Location: " .$answer[1]);
                } else {
                    print "Unknown mode";
                }
            } else {
                header("Location: " . BX_WEBROOT."admin/?back=".urlencode($_SERVER['REQUEST_URI']));
            }
            
            die();
        } 
        
        $mode = "default";
        
        if (isset($_GET['answer']) && $_GET['answer'] == 'yes') {
            $info = bx_helpers_openid::getRequestInfo();
            if ($_GET['always'] == 'true') {
                $query = "insert into ".$GLOBALS['POOL']->config->getTablePrefix()."openid_uri (date, uri) value(now(), ".$GLOBALS['POOL']->db->quote($info->args['openid.trust_root']).")";
                $res = $GLOBALS['POOL']->db->query($query);
            }
            $response = $info->answer(true);
            $this->handleAnswer($server, $response);
            die();
        }
        
        if (isset($_GET['answer']) && $_GET['answer'] == 'no') {
            $server = bx_helpers_openid::getServer();
            $info = bx_helpers_openid::getRequestInfo();
            header("Location: ". $info->getCancelURL());
            die();
            
        }
        
        if(isset($_GET['openid_mode'])) {
            $httpmethod = "GET";
        } else {
            $httpmethod = $_SERVER['REQUEST_METHOD'];
        }
        
        if ($httpmethod == 'GET') {
            $request = $_GET;
        } else {
            $request = $_POST;
        }
        
        $request = Auth_OpenID::fixArgs($request);
        $request = $server->decodeRequest($request);
        
        $test = bx_helpers_openid::setRequestInfo($request);
        
        if (!$request && !isset($_GET['answer'])) {
            
            $xml = $this->do_about($tablePrefix);
            if(isset($_POST['UserProfileForm'])) {
                $xml .= $this->getUserEditForm($tablePrefix, $_POST);
            } else {
                $xml .= $this->getUserEditForm($tablePrefix);
            }
            
            $xml .= "</body></html>";
            $dom = new DomDocument();
            $dom->loadXML($xml);
            return $dom;
            die();
        }
        
        if (in_array($request->mode, array('checkid_immediate', 'checkid_setup'))) {
            if (bx_openIdIsTrusted($request->identity, $request->trust_root)) {            
                    $response = $request->answer(true);    
            }  else if ($request->immediate) {
                $response =& $request->answer(false, bx_helpers_openid::getServerURL());
            } else {
                if (!$permObj->isAllowed('/',array('admin'))) {
                    header("Location: " . BX_WEBROOT."admin/?back=".urlencode($_SERVER['REQUEST_URI']));
                    die();
                }
                $xml = $this->do_auth($request, $tablePrefix);
                $xml .= $this->getUserForm();
                $xml .= "</body></html>";
                $dom = new DomDocument();
                $dom->loadXML($xml);
                return $dom;
                die();
            }
        } else {
            $response = $server->handleRequest($request);
        }
        if(isset($server) and isset($response)) {
            $this->handleAnswer($server, $response);
        }
    }
    
    public function handleAnswer($server, $response) {
        $answer = $server->encodeResponse($response);
        foreach ($answer->headers as $k => $v) {
            header("$k: $v");
        }
        
        $server = bx_helpers_openid::getServer();
        
        switch ($answer->code) {
        
        case AUTH_OPENID_HTTP_REDIRECT:
            //header("Location: " . $answer[1]);
            break;
        case AUTH_OPENID_HTTP_OK:
            header('HTTP/1.1 200 OK');
            header('Connection: close');
            header('Content-Type: text/plain; charset=us-ascii');
            print $answer->body;
            bx_helpers_debug::dump_errorlog($answer->body);
            die();
            break;
        case AUTH_OPENID_HTTP_ERROR:
            if (isset($_POST['username'])) {
                print '<meta http-equiv="refresh" content="1; URL=http://'.$_SERVER['HTTP_HOST'].'/admin/webinc/openid">';
            } else {
                header( 'HTTP/1.1 400 Bad Request');
                header('Connection: close');
                header('Content-Type: text/plain; charset=us-ascii');
                print $answer[1];
                die();
            }
            break;
        default:
            print $answer[0] ."<h2 class='openIdPage'> mode not implemented.</h2>";
            
        }
        //print "</html>";
    }

    static function do_about($tablePrefix) {
        $xml = '';
        if(isset($_GET['id'])) {
            
            $dquery = "delete from ". $GLOBALS['POOL']->config->getTablePrefix(). "openid_uri where id = '". (int) $_GET['id']."'";
            $GLOBALS['POOL']->db->query($dquery);
            $xml .= '<meta http-equiv="refresh" content="1; URL=http://'.$_SERVER['HTTP_HOST'].'/admin/webinc/openid">';
        }
        
        $query = "select * from ". $tablePrefix . "openid_uri";
        $result = $GLOBALS['POOL']->db->query($query);
        $xml .= bx_plugins_admin_openid::printHeader();
        $xml .= '<body>';
        $xml .= '<h2 class="openIdPage">'. bx_helpers_config::getOption('sitename'). ' - Flux CMS OpenID</h2>';
        $xml .= "<div class='openIdTrust'>";
        $xml .= '<h3>Trusted Sites</h3>';
        $xml .= "<table>";
        while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .= "<tr><td><a href='?id=".$row['id']."'><img style='border:0px;' src='".BX_WEBROOT."admin/webinc/img/icons/delete.gif'/></a></td><td>".$row['uri']."</td><td>".$row['date']."</td></tr>\n";
        }
        $xml .= "</table>";
        $xml .= "</div>";
        
        return $xml;
    }
    
    static function do_auth($request, $tablePrefix) {
        $xml = bx_plugins_admin_openid::printHeader();
        $xml .= '<body>';
        $xml .= '<h2 class="openIdPage">'. bx_helpers_config::getOption('sitename'). ' - Flux CMS OpenID</h2>';
        $xml .= "<div class='openIdTrust'><p style='padding-left: 20px; margin:0px;'>Please authorize ".$request->trust_root.'</p>';
        $xml .= '<br/>';
        $xml .= "<p style='padding-left: 20px; margin:0px;'>Do you want to trust " . $request->trust_root ."?</p>";
        $xml .= '<br/>';
        $xml .= '<a  style="padding-left: 20px; " href="?answer=yes&#38;always=true">Always yes</a> | <a href="?answer=yes">yes</a> | <a href="?answer=no">no</a> ';
        $xml .= '</div>';
        //$xml .= '<h2 class="openIdPage"></h2>';
        return $xml;
        
        
    }
    
    static function printHeader() {
        
        $xml = '<html>';
        $xml .= '<head>';
        $xml .= '<link type="text/css" href="'.BX_WEBROOT.'/themes/standard/admin/css/formedit.css" rel="stylesheet"/>';
        $xml .= '<script src="'.BX_WEBROOT.'webinc/js/openId.js" type="text/javascript"></script>';
        $xml .= '</head>';
        
        return $xml;
        
    }
    
    static function getUserEditForm($tablePrefix, $data=null) {
        $userid = bx_helpers_perm::getUserId();
        
        $profiles_query = "select * from ". $tablePrefix . "openid_profiles where userid = '".$userid."'";
        $profiles_res = $GLOBALS['POOL']->db->query($profiles_query);
        
        $xml = "<div class='openIdTrust'>";
        
        $xml .= '<form action="" method="post" class="personaform" name="personaForm">
        <select name="profiles" onchange="submit();">';
        
        while ($profiles_row = $profiles_res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            if(isset($data['profiles']) && $data['UserProfileForm'] == 1 && $data['profiles'] == $profiles_row['id']) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
            }
            
            
            
            if($profiles_row['standard'] == 'on' && !isset($data['profiles']) && $data['UserProfileForm'] != 1) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
            } elseif($data['profiles'] != $profiles_row['id']) {
                $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
            }
        }
        
        
        //vielleicht doppelter code
        if(isset($data['profiles']) && $data['UserProfileForm'] == 1) {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where id = '".$data['profiles']."'";
        } else {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where standard = 'on' and userid = '".$userid."'";
        }
        
        
        $default_profile_res = $GLOBALS['POOL']->db->query($default_profile_query);
        
        $default_profile_row = $default_profile_res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        $xml .= '</select><input type="text" name="UserProfileForm" style="display:none" value="1"/></form>';
        
        $xml .= '<h3>Personas</h3>';
        $xml .= '<form action="" method="post">';
        $xml .= '<input type="text" name="UserEditForm" style="display:none" value="1"/>';
        $xml .= '<table>';
        
        $xml .= '<tr><td>';
        $xml .= 'Persona Name';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="persona" value="'.$default_profile_row['persona'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= '<input type="checkbox" name="default" value="'.$default_profile_row['standard'].'"/>';
        $xml .= '</td><td>';
        $xml .= 'Make this my default persona';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Nickname';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="nickname" value="'.$default_profile_row['nickname'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Full Name';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="name" value="'.$default_profile_row['name'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'E-Mail Adress';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="mail" value="'.$default_profile_row['mail'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Birth date';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="bla" value="'.$default_profile_row['birthdate'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Postal Code';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="postal" value="'.$default_profile_row['postal'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Gender';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="gender" value="'.$default_profile_row['gender'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Country';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="country" value="'.$default_profile_row['country'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Time Zone';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="timezone" value="'.$default_profile_row['timezone'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Preferred Language';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="lang" value="'.$default_profile_row['lang'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr>';
        $xml .= '<td colspan="2">';
        $xml .= '<input type="submit" name="save"/>';
        $xml .= '</td></tr>';
        
        $xml .= '</table>';
        $xml .= '</form>';
        $xml .= '</div>';
        
        return $xml;
    }
    
    static function getUserForm() {
        $xml = "<div class='openIdTrust'>";
        $xml .= '<h3>Personas</h3>';
        $xml .= '<form action="" method="post">';
        $xml .= '<table>';
        
        $xml .= '<tr><td>';
        $xml .= 'Persona Name';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="persona"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= '<input type="checkbox" name="default"/>';
        $xml .= '</td><td>';
        $xml .= 'Make this my default persona';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Nickname';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="nickname"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Full Name';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="name"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'E-Mail Adress';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="mail"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Birth date';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="bla"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Postal Code';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="postal"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Gender';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="gender"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Country';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="country"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Time Zone';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="timezone"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Preferred Language';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="lang"/>';
        $xml .= '</td></tr>';
        
        $xml .= '</table>';
        $xml .= '</form>';
        $xml .= '</div>';
        
        return $xml;
    }
    
    static function saveUserProfile($data, $tablePrefix) {
        
        $userid = bx_helpers_perm::getUserId();
        
        if($data['default'] == 'on') {
            $remove_default_query = 'update '.$tablePrefix.'openid_profiles set standard = 0 where standard = "on" and userid = "'.$userid.'"';
            $GLOBALS['POOL']->db->query($remove_default_query);
            
        }
        
        $insert_query = 'insert into '.$tablePrefix.'openid_profiles (persona , nickname , name 
        , mail , birthdate , postal , gender , country , timezone , lang , standard , userid) 
        values("'.$data['persona'].'" , "'.$data['nickname'].'" , "'.$data['name'].'" 
        , "'.$data['mail'].'" , "'.$data['bla'].'" , "'.$data['postal'].'" , "'.$data['gender'].'" , "'.$data['country'].'" 
        , "'.$data['timezone'].'" , "'.$data['lang'].'" , "'.$data['default'].'" , "'.$userid.'")';
        
        $GLOBALS['POOL']->db->query($insert_query);
        
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

    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
}
?>
