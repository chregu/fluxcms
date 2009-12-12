<?php
include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);


if (!$permObj->isAllowed('/',array('admin')) &&  !(isset($_POST['openid_mode']) && $_POST['openid_mode'] == 'associate')) {

    header("Location: " . BX_WEBROOT."admin/?back=".urlencode($_SERVER['REQUEST_URI']));
    die();
}

    
if ($_GET['answer'] == 'yes') {

    
    $info = bx_helpers_openid::getRequestInfo();
    $server = bx_helpers_openid::getServer();
    if ($_GET['always'] == 'true') {
        $query = "insert into ".$GLOBALS['POOL']->config->getTablePrefix()."openid_uri (date, uri) value(now(), ".$GLOBALS['POOL']->db->quote($info->args['openid.trust_root']).")";
        $res = $GLOBALS['POOL']->db->query($query);
    }

    
    $answer = $server->getAuthResponse(&$info, true);
    if ($answer[0] == 'redirect') {
        header("Location: " . $answer[1]);
    } else {
        print "An error occured. ".bx_helpers_debug::webdump($answer);
    }
    die();

}

if ($_GET['answer'] == 'no') {
    
    $info = bx_helpers_openid::getRequestInfo();
    header("Location: ". $info->getCancelURL());
    die();
    
}
