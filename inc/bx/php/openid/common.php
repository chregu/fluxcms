<?php


include_once("../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../..");

require_once "Auth/OpenID/Consumer.php";

require_once "Auth/OpenID/FileStore.php";


$store_path = BX_TEMP_DIR."_php_consumer_test";

if (!file_exists($store_path) &&
    !mkdir($store_path)) {
    print "Could not create the FileStore directory '$store_path'. ".
        " Please check the effective permissions.";
    exit(0);
}

$store = new Auth_OpenID_FileStore($store_path);

$consumer = new Auth_OpenID_Consumer($store, null,$immediate);

