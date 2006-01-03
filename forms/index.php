<?php
include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");

include_once ("../../inc/bx/permm.php");
@session_start();
$perm = bx_permm::getInstance($GLOBALS['POOL']->config['permm']);
if ($perm instanceof bx_permm) {
    $perm->start();
    if ($perm->getAuth() !== FALSE && $perm->isAllowed("/admin/forms/",array("admin"))) {
        try {
            new bx_editors_dbform_main();
        } catch (Exception $e) {
            print "<h1> DB Excpetion</h1>";
            
            print $e->getMessage();
            print "<br/>";
            print $e->userInfo;
            print "<hr/>";
            print "<pre>";
            print $e;
        }
    } else {
        header("Location: /admin/?edit=".bx_helpers_uri::getRequestUri());
        exit(0);
    }
    
    
} else {
    header("Location: /admin/?edit=".bx_helpers_uri::getRequestUri());
    exit(0);
}

?>
