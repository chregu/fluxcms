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



switch ($mode) {
    default: 
    $server = bx_helpers_openid::getServer();
    $answer = $server->getOpenIDResponse('bx_openIdIsTrusted',$method);
    switch ($answer[0]) {
        
        case 'do_auth':
            print "not yet done, authorize ".$answer[1]->args['openid.trust_root'];
            bx_helpers_openid::setRequestInfo($answer[1]);
            print '<br/>';
            print "Do you want to trust " . $answer[1]->args['openid.trust_root'] ."?";
            print '<br/>';
            print '<a href="./trust.php?answer=yes&always=true">Always yes</a> | <a href="./trust.php?answer=yes">yes</a> | <a href="./trust.php?answer=no">no</a> ';
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
            header( 'HTTP/1.1 400 Bad Request');
            header('Connection: close');
            header('Content-Type: text/plain; charset=us-ascii');
            print $answer[1];
            die();
            break;
        default:
            print $answer[0] ." mode not implemented.";
            
    }
    
    }






?>
