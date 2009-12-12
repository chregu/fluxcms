<?php

include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");
$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);
if (!$permObj->isAllowed('/',array('admin', 'edit'))) {
    
    print "Access denied";
    die();
}

if ($GLOBALS['POOL']->config->adminDeleteTmp == 'true') {
    bx_helpers_file::rmdir(BX_TEMP_DIR,false);
    
    print BX_TEMP_DIR . " deleted.";
} else {
    
    print "not allowed";
    
}
