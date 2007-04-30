<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: LIIP AG <beni@liip.ch>                              |
// +----------------------------------------------------------------------+
/**
 * class bx_plugins_admin_openid
 * @package bx_plugins_admin
 * @subpackage openid
 */
class bx_plugins_admin_openid extends bx_plugins_admin implements bxIplugin  {
    static private $instance = null;
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_plugins_admin_openid($mode);
        } 
        
        return self::$instance;
    }
    
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
        //OpenID Server Objekt
        $server = bx_helpers_openid::getServer();
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $conf = bx_config::getInstance();
        $userid = bx_helpers_perm::getUserId();
        
        $confvars = $conf->getConfProperty('permm');
        $permObj = bx_permm::getInstance($confvars);
        
        //die profileid wird auf NULL gestellt da wodurch das standard profile angezeigt wird
        if(!isset($this_profile_id)) {
            $this_profile_id = null;
        }
        
        //hier werden die profile der user gelöscht, aktualisiert oder erstellt
        if(isset($_POST['UserEditForm'])) {
            if(isset($_POST['delete']) && $_POST['delete'] == 'Delete') {
                $this->deleteUserProfile($_POST, $tablePrefix);
            } else {
                if($_POST['save'] == 'Save') {
                    $this_profile_id = $this->saveUserProfile($_POST, $tablePrefix);
                }
                if($_POST['save'] == 'Update') {
                    $this_profile_id = $this->updateUserProfile($_POST, $tablePrefix);
                }
            }
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
        
        
        //Details des standard oder ausgewählten Profil
            if(!isset($_POST['profiles']) && isset($_SESSION["openid.sreg.optional"])) {
                //liest das default profil aus der Datenbank
                $profile_query = "select persona as persona_name , nickname as nickname , name as fullname , mail as email , birthdate , postal as postcode , gender , country , timezone , lang as language 
                from ". $tablePrefix . "openid_profiles where standard = 'on' and userid = '".$userid."'";
                $profile_res = $GLOBALS['POOL']->db->query($profile_query);
                $profile_row = $profile_res->fetchRow(MDB2_FETCHMODE_ASSOC);
            } elseif(isset($_POST['profiles']) && isset($_SESSION["openid.sreg.optional"])) {
                //liest das ausgewählte profil aus der Datenbank
                $profile_query = "select persona as persona_name , nickname as nickname , name as fullname , mail as email , birthdate , postal as postcode , gender , country , timezone , lang as language 
                from ". $tablePrefix . "openid_profiles where id = '".$_POST['profiles']."' and userid = '".$userid."'";
                $profile_res = $GLOBALS['POOL']->db->query($profile_query);
                $profile_row = $profile_res->fetchRow(MDB2_FETCHMODE_ASSOC);
            }
            
            
        
        
        if (isset($_GET['answer']) && $_GET['answer'] == 'yes') {
            $info = bx_helpers_openid::getRequestInfo();
            if (isset($_GET['always']) && $_GET['always'] == 'true') {
                //fügt einen Host als "always trusted" hinzu
                $query = "insert into ".$GLOBALS['POOL']->config->getTablePrefix()."openid_uri (date, uri) value(now(), ".$GLOBALS['POOL']->db->quote($info->trust_root).")";
                $res = $GLOBALS['POOL']->db->query($query);
            }
            
            
            //Bestätigung nach erfolgreicher Anmeldung
            $response = $info->answer(true);
            
            if (is_array($_SESSION["openid.sreg.optional"])) {
                foreach ($_SESSION["openid.sreg.optional"] as $k => $v) {
                    $response->addField('sreg', $v, 
                                        $profile_row[$v]);
                }
            }
            
            $this->handleAnswer($server, $response);
            die();
        }
        
        if (isset($_GET['answer']) && $_GET['answer'] == 'no') {
            $info = bx_helpers_openid::getRequestInfo();
            //Abbruch der Anmeldung
            header("Location: ". $info->getCancelURL());
            die();
            
        }
        
        //HTTP Methode
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
        
        //OpenID Request Handling
        $request = Auth_OpenID::fixArgs($request);
        $_SESSION["openid.sreg.optional"] = split(",", $request['openid.sreg.optional']);
        $request = $server->decodeRequest($request);
        
        bx_helpers_openid::setRequestInfo($request);
        
        if (!$request && !isset($_GET['answer'])) {
            //Anzeigen der immer Erlaubten Seiten
            $xml = $this->do_about($tablePrefix);
            
            //Anzeigen und Editieren der Profile des jeweiligen Users
            if(isset($_POST['UserProfileForm'])) {
                $xml .= $this->getUserEditForm($tablePrefix, $this_profile_id, $_POST);
            } else {
                $xml .= $this->getUserEditForm($tablePrefix, $this_profile_id);
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
                
                //Anzeigen der Profile des jeweiligen Users wenn von redirect kommend
                if(isset($_POST['UserProfileForm']) || isset($_POST['editId'])) {
                    if(isset($_POST['profiles']) && $_POST['profiles'] == 'new'  || isset($_POST['editId'])) {
                        if(isset($_POST['editId'])) {
                            $this_profile_id = $_POST['editId'];
                        }
                        $xml .= $this->getUserEditForm($tablePrefix, $this_profile_id, $_POST);
                    } else {
                        $xml .= $this->getUserForm($tablePrefix, $this_profile_id, $_POST);
                    }
                } else {
                    $xml .= $this->getUserForm($tablePrefix, $this_profile_id);
                }
                
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
    
    // Antwort verarbeiten und die entsprechenden header schicken
    public function handleAnswer($server, $response) {
        $answer = $server->encodeResponse($response);
        foreach ($answer->headers as $k => $v) {
            header("$k: $v");
        }
        
        switch ($answer->code) {
        
        case AUTH_OPENID_HTTP_REDIRECT:
            //header("Location: " . $answer[1]);
            break;
        case AUTH_OPENID_HTTP_OK:
            header('HTTP/1.1 200 OK');
            header('Connection: close');
            header('Content-Type: text/plain; charset=us-ascii');
            print $answer->body;
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
    }

    static function do_about($tablePrefix) {
        $xml = '';
        if(isset($_GET['id'])) {
            
            $dquery = "delete from ". $GLOBALS['POOL']->config->getTablePrefix(). "openid_uri where id = '". (int) $_GET['id']."'";
            $GLOBALS['POOL']->db->query($dquery);
            $xml .= '<meta http-equiv="refresh" content="1; URL=http://'.$_SERVER['HTTP_HOST'].'/admin/openid">';
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
    
    /*
        static function getUserEditForm
        $tablePrefix -> Datenbank Tabellenprefix
        $this_profile_id(null) -> ID des auswählten oder editierten Profiles
        $data(null) -> $_POST
        $profiles_row -> Alle Profile
        $default_profile_row -> Ausgewähltes, gespeichertes oder DEFAULT Profile
        $this_profile_id -> Id für den nächsten Datenbank Eintrag
    */
    static function getUserEditForm($tablePrefix, $this_profile_id=null, $data=null) {
        $userid = bx_helpers_perm::getUserId();
        $profiles_query = "select * from ". $tablePrefix . "openid_profiles where userid = '".$userid."'";
        $profiles_res = $GLOBALS['POOL']->db->query($profiles_query);
        
        $xml = "<div class='openIdTrust'>";
        
        $xml .= '<form action="" method="post" class="personaform" name="personaForm">
        <select name="profiles" onchange="submit();">';
        // Ausgewähltes oder DEFAULT Profile anzeigen
        
        while ($profiles_row = $profiles_res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            
            if(isset($data['profiles']) && $data['UserProfileForm'] == 1 && $data['profiles'] == $profiles_row['id']) {
                if($profiles_row['standard'] == 'on') {
                    $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'  (DEFAULT)</option>';
                } else {
                    $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' </option>';
                }
            } 
            if($profiles_row['standard'] == 'on' && !isset($data['profiles']) && $data['UserProfileForm'] != 1 && !isset($this_profile_id)) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
            } elseif($profiles_row['id'] == $this_profile_id) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' </option>';
            } elseif($data['profiles'] != $profiles_row['id']) {
                if($profiles_row['standard'] == 'on') {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
                } else {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
                }
            }
            
            /* TAKE A LOOK */
        /*    if(isset($data['profiles']) && isset($data['UserProfileForm']) && $data['UserProfileForm'] == 1 && $data['profiles'] == $profiles_row['id']) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' </option>';
            } 
            if($profiles_row['standard'] == 'on' && !isset($data['profiles']) && isset($data['UserProfileForm']) && $data['UserProfileForm'] != 1 && !isset($this_profile_id)) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
            } elseif($profiles_row['id'] == $this_profile_id) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
            bx_helpers_debug::webdump($data);
            bx_helpers_debug::webdump($profiles_row);
                    
            } elseif(isset($data['profiles']) && $data['profiles'] != $profiles_row['id']) {
                if($profiles_row['standard'] == 'on') {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
                } else {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
                }
            }*/
            
            
        }
        if($data['profiles'] == 'new') {
            $xml .= '<option selected="selected" value="new">New</option>';
        } elseif($data['profiles'] != 'new') {
            $xml .= '<option value="new">New</option>';
        }
            
        $xml .= '</select><input type="text" name="UserProfileForm" style="display:none" value="1"/></form>';
        
        if(isset($this_profile_id)) {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where id = '".$this_profile_id."'";
        } elseif(isset($data['profiles']) && $data['UserProfileForm'] == 1 && !isset($this_profile_id)) {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where id = '".$data['profiles']."'";
        } else {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where standard = 'on' and userid = '".$userid."'";
        }
        
        
        $default_profile_res = $GLOBALS['POOL']->db->query($default_profile_query);
        
        $default_profile_row = $default_profile_res->fetchRow(MDB2_FETCHMODE_ASSOC);
        //Profile Edit/New Formular
        $xml .= '<h3>Personas</h3>';
        $xml .= '<form action="" method="post">';
        if(isset($this_profile_id)) {
                $xml .= '<input type="text" name="UserProfileId" style="display:none" value="'.$this_profile_id.'"/>';
        } else {
            if(isset($data['profiles']) && $data['profiles'] != 'new') {
                $xml .= '<input type="text" name="UserProfileId" style="display:none" value="'.$default_profile_row['id'].'"/>';
            } else {
                $next_insert_id = $GLOBALS['POOL']->dbwrite->nextID($tablePrefix."_openid_profiles");
                $xml .= '<input type="text" name="UserProfileIdNext" style="display:none" value="'.$next_insert_id.'"/>';
            }
        }
        
        $xml .= '<input type="text" name="UserEditForm" style="display:none" value="1"/>';
        $xml .= '<table>';
        
        $xml .= '<tr><td>';
        $xml .= 'Persona Name';
        $xml .= '</td><td>';
        $xml .= '<input type="text" name="persona" value="'.$default_profile_row['persona'].'"/>';
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        if($default_profile_row['standard'] == 'on') {
            $xml .= '<input type="checkbox" name="default" checked="checked" value="'.$default_profile_row['standard'].'"/>';
        } else {
            $xml .= '<input type="checkbox" name="default"/>';
        }
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
        $xml .= 'Birth date (yyyy-mm-dd)';
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
        
        if(isset($default_profile_row['id'])) {
            $xml .= '<tr><td>';
            $xml .= '<input type="submit" name="save" value="Update"/>';
            $xml .= '</td><td>';
            $xml .= '<input type="submit" name="delete" value="Delete"/>';
            $xml .= '</td></tr>';
        } else {
            $xml .= '<tr><td colspan="2">';
            $xml .= '<input type="submit" name="save" value="Save"/>';
            $xml .= '</td>';
            $xml .= '</tr>';
        }
        
        $xml .= '</table>';
        $xml .= '</form>';
        $xml .= '</div>';
        
        return $xml;
    }
    
    static function getUserForm($tablePrefix, $this_profile_id=null, $data=null) {
        
        //Id de aktiven Users
        $userid = bx_helpers_perm::getUserId();
        
        // Liste der User Profile
        $xml = "<div class='openIdTrust'>";
        $profiles_query = "select * from ". $tablePrefix . "openid_profiles where userid = '".$userid."'";
        $profiles_res = $GLOBALS['POOL']->db->query($profiles_query);
        
        $xml = "<div class='openIdTrust'>";
        
        $xml .= '<form action="" method="post" class="personaform" name="personaForm">
        <select name="profiles" onchange="submit();">';
        
        while ($profiles_row = $profiles_res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            
            if(isset($data['profiles']) && $data['UserProfileForm'] == 1 && $data['profiles'] == $profiles_row['id']) {
                if($profiles_row['standard'] == 'on') {
                    $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'  (DEFAULT)</option>';
                } else {
                    $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' </option>';
                }
            } 
            if($profiles_row['standard'] == 'on' && !isset($data['profiles']) && $data['UserProfileForm'] != 1 && !isset($this_profile_id)) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
            } elseif($profiles_row['id'] == $this_profile_id) {
                $xml .= '<option selected="selected" value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' </option>';
            } elseif($data['profiles'] != $profiles_row['id']) {
                if($profiles_row['standard'] == 'on') {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].' (DEFAULT)</option>';
                } else {
                    $xml .= '<option value="'.$profiles_row['id'].'">'.$profiles_row['persona'].'</option>';
                }
            }
        }
        if($data['profiles'] != 'new') {
            $xml .= '<option value="new">New</option>';
        }
            
        $xml .= '</select><input type="text" name="UserProfileForm" style="display:none" value="1"/></form>';
        
        
        if(isset($this_profile_id)) {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where id = '".$this_profile_id."'";
        } elseif(isset($data['profiles']) && $data['UserProfileForm'] == 1 && !isset($this_profile_id)) {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where id = '".$data['profiles']."'";
        } else {
            $default_profile_query = "select * from ". $tablePrefix . "openid_profiles where standard = 'on' and userid = '".$userid."'";
        }
        
        
        $default_profile_res = $GLOBALS['POOL']->db->query($default_profile_query);
        
        $default_profile_row = $default_profile_res->fetchRow(MDB2_FETCHMODE_ASSOC);
        bx_helpers_debug::webdump($_SESSION);
        
        $xml .= '<h3>Personas</h3>';
        
        $xml .= '<table>';
        
        $xml .= '<tr><td>';
        $xml .= 'Persona:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['persona'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Nickname:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['nickname'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Name:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['name'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'E-Mail:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['mail'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Birth Date:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['birthdate'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Postal Code:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['postal'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Gender:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['gender'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Country:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['country'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Timezone:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['timezone'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td>';
        $xml .= 'Language:';
        $xml .= '</td><td>';
        $xml .= $default_profile_row['lang'];
        $xml .= '</td></tr>';
        
        $xml .= '<tr><td colspan="2"><form action="" method="post">';
        $xml .= '<input type="text" name="editId" value="'.$default_profile_row['id'].'" style="display:none"/>';
        $xml .= '<input type="submit" name="edit" value="Edit" onclick="submit();"/>';
        $xml .= '</form></td>';
        $xml .= '</tr>';
        
        $xml .= '</table>';
        
        $xml .= '</div>';
        
        return $xml;
    }
    
    static function saveUserProfile($data, $tablePrefix) {
        $userid = bx_helpers_perm::getUserId();
        
        $check_default_query = 'select * from '.$tablePrefix.'openid_profiles where standard = "on" and userid = "'.$userid.'"';
        $check_default_res = $GLOBALS['POOL']->db->query($check_default_query);
        $check_default__row = $check_default_res->fetchRow(MDB2_FETCHMODE_ASSOC);
        
        if(isset($data['default']) && $data['default'] == 'on') {
            $remove_default_query = 'update '.$tablePrefix.'openid_profiles set standard = 0 where standard = "on" and userid = "'.$userid.'"';
            $GLOBALS['POOL']->db->query($remove_default_query);
        }
        
        if(isset($data['UserProfileIdNext'])) {
            $insert_query = 'insert into '.$tablePrefix.'openid_profiles (id , persona , nickname , name 
            , mail , birthdate , postal , gender , country , timezone , lang , standard , userid) 
            values("'.$data['UserProfileIdNext'].'" , "'.$data['persona'].'" , "'.$data['nickname'].'" , "'.$data['name'].'" 
            , "'.$data['mail'].'" , "'.$data['bla'].'" , "'.$data['postal'].'" , "'.$data['gender'].'" , "'.$data['country'].'" 
            , "'.$data['timezone'].'" , "'.$data['lang'].'" ';
        } else {
            $insert_query = 'insert into '.$tablePrefix.'openid_profiles (persona , nickname , name 
            , mail , birthdate , postal , gender , country , timezone , lang , standard , userid) 
            values("'.$data['persona'].'" , "'.$data['nickname'].'" , "'.$data['name'].'" 
            , "'.$data['mail'].'" , "'.$data['bla'].'" , "'.$data['postal'].'" , "'.$data['gender'].'" , "'.$data['country'].'" 
            , "'.$data['timezone'].'" , "'.$data['lang'].'" ';
        }
        
        if(isset($data['default'])) {
            $insert_query .= ', "'.$data['default'].'" ';
        } elseif($check_default__row['standard'] != 'on') {
            $insert_query .= ', "on" ';
        } else {
            $insert_query .= ', "0" ';
        }
        
        $insert_query .= ', "'.$userid.'")';
        
        $GLOBALS['POOL']->db->query($insert_query);
        
        if(isset($data['UserProfileIdNext'])) {
            return $data['UserProfileIdNext'];
        }
        
    }
    
    static function updateUserProfile($data, $tablePrefix) {
        $userid = bx_helpers_perm::getUserId();
        
        if(isset($data['default']) && $data['default'] == 'on') {
            $remove_default_query = 'update '.$tablePrefix.'openid_profiles set standard = 0 where standard = "on" and userid = "'.$userid.'"';
            $GLOBALS['POOL']->db->query($remove_default_query);
            
        }
        
        
        $update_query = 'update '.$tablePrefix.'openid_profiles set persona = "'.$data['persona'].'" , nickname = "'.$data['nickname'].'" , name = "'.$data['persona'].'" 
        , mail = "'.$data['mail'].'" , birthdate = "'.$data['bla'].'" , postal = "'.$data['postal'].'" , gender = "'.$data['gender'].'" , country = "'.$data['country'].'" 
        , timezone = "'.$data['timezone'].'" , lang = "'.$data['lang'].'" ' ;
        
        if(isset($data['default'])) {
            $update_query .= ', standard = "'.$data['default'].'" ';
        } else {
            $update_query .= ', standard = "0" ';
        }
        
        $update_query .= ' where userid = "'.$userid.'" and id = "'.$data['UserProfileId'].'"';
        $GLOBALS['POOL']->db->query($update_query);
        
        return $data['UserProfileId'];
    }
    
    static function deleteUserProfile($data, $tablePrefix) {
        $userid = bx_helpers_perm::getUserId();
        
        $delete_query = 'delete from '.$tablePrefix.'openid_profiles where userid = "'.$userid.'" and id = "'.$data['UserProfileId'].'"';
        $GLOBALS['POOL']->db->query($delete_query);
        
    }
    
    static function getSreg($identity){
        // from config.php
        global $openid_sreg;
    
        if (!is_array($openid_sreg)) {
            return null;
        }
    
        return $openid_sreg[$identity];
    
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
