<?php

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");
$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin')) &&  !(isset($_POST['openid_mode']) && ($_POST['openid_mode'] == 'associate' || $_POST['openid_mode'] == 'check_authentication'))) {
    if (isset($_GET["openid_mode"]) && $_GET["openid_mode"]== 'checkid_immediate') {
        $server = bx_helpers_openid::getServer();
        $answer = $server->getOpenIDResponse(false,"GET");
        
        if ($answer[0] == "redirect") {
            header("Location: " .$answer[1]);
        } else {
            print "Unknown mode";
            bx_helpers_debug::webdump($answer);
        }
    } else {
        header("Location: " . BX_WEBROOT."admin/?back=".urlencode($_SERVER['REQUEST_URI']));
    }
    
    die();
} 
$mode = "default";
if (isset($_GET['openid_mode'])) {
    $method = "GET";
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}
print '<html>';
print '<head>';
print '<link type="text/css" href="'.BX_WEBROOT.'/themes/standard/admin/css/formedit.css" rel="stylesheet"/>';
print '</head>';



switch ($mode) {
    default: 
    $server = bx_helpers_openid::getServer();
    $answer = $server->getOpenIDResponse('bx_openIdIsTrusted',$method);
    switch ($answer[0]) {
        
        case 'do_auth':
            print '<h2 class="openIdPage">OpenID</h2>';
            print "<div id='openIdTrust'><p style='padding-left: 20px; margin:0px;'>not yet done, authorize ".$answer[1]->args['openid.trust_root'].'</p>';
            bx_helpers_openid::setRequestInfo($answer[1]);
            print '<br/>';
            print "<p style='padding-left: 20px; margin:0px;'>Do you want to trust " . $answer[1]->args['openid.trust_root'] ."?</p>";
            print '<br/>';
            print '<a  style="padding-left: 20px; " href="./trust.php?answer=yes&always=true">Always yes</a> | <a href="./trust.php?answer=yes">yes</a> | <a href="./trust.php?answer=no">no</a> ';
            print '</div>';
            print '<h2 class="openIdPage"></h2>';
            break;
        case 'redirect':
            header("Location: " . $answer[1]);
            break;
        case 'remote_ok':
            header('HTTP/1.1 200 OK');
            header('Connection: close');
            header('Content-Type: text/plain; charset=us-ascii');
            print $answer[1];
            die();
            break;
        case 'remote_error':
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
        case 'do_about':
    
            if(isset($_GET['id'])) {
                $dquery = "delete from fluxcms_openid_uri where id = '".$_GET['id']."'";
                $GLOBALS['POOL']->db->query($dquery);
                print '<meta http-equiv="refresh" content="1; URL=http://'.$_SERVER['HTTP_HOST'].'/admin/webinc/openid">';
            }
            
            $query = "select * from fluxcms_openid_uri";
            $result = $GLOBALS['POOL']->db->query($query) or die("false select query");
            print '<h2 class="openIdPage">OpenID</h2>';
            print "<div id='openIdTrust'>";
            print "<table>";
            while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                print "<tr><td><a href='?id=".$row['id']."'><img style='border:0px;' src='/webinc/images/delete.gif'/></a></td><td>".$row['uri']."</td><td>".$row['date']."</td></tr>\n";
            }
            print "</table>";
            print "</div>";
            break;
        default:
            print $answer[0] ."<h2 class='openIdPage'> mode not implemented.</h2>";
            
    }
    print "</html>";
    }






?>
