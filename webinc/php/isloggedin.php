<?php
include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin'))) {
    die("false");
}
die("true");

