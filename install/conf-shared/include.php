<?php


$GLOBALS['POOL']->config->setOutputCacheCallback("bx_cachecallback");


function bx_cachecallback() {
   if (strpos($_GET['path'],"admin/") !== false) {
        return false;
        }
    return 304;
}

?>
