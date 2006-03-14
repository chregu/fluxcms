<?php

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);
if (!$permObj->isAllowed('/',array('admin')) && $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: " . BX_WEBROOT."admin/?back=".urlencode($_SERVER['REQUEST_URI']));
    die();
}

switch ($mode) {
    default: 
    $server = bx_helpers_openid::getServer();
    
    $answer = $server->getOpenIDResponse('isTrusted');
    switch ($answer[0]) {
        case 'do_auth':
            print "not yet done, authorize ".$answer[1]->args['openid.trust_root'];
            break;
        case 'redirect':
            header("Location: " . $answer[1]);
            break;
        default:
            print $answer[0] ." mode not implemented.";
            
    }
    
    }






?>