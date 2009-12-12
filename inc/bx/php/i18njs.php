<?php

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");



$lang = substr($_GET['lang'],0,2);
$src = 'lang/master';
if(defined('BX_OPEN_BASEDIR') && !(substr($src,0,1) == '/' || substr($src,1,1) == ":")) {
    $src = BX_OPEN_BASEDIR.$src;
}


if (!$cat = @domdocument::load($src.'_'.$lang.'.xml')) {
    $lang = substr($lang,0,-(strrpos($lang,"_")+1));
    if (!$cat = @domdocument::load($src.'_'.$lang.'.xml')) {
        $cat = @domdocument::load($src.'.xml');
    }
}

if($cat instanceof DOMDocument) {
    // resolve xincludes
    $cat->xinclude();
    $catctx = new DomXpath($cat);
}

$langdefs = $catctx->query("/catalogue/message[@ref='js']");
if ($langdefs->length > 0) {
    $langarr = array();
    foreach($langdefs as $langdef) {
        $langarr[$langdef->getAttribute('key')]=addslashes($langdef->nodeValue);
    }
    
    
    
    
}

print 'var i18n = ' . bx_helpers_json::encode($langarr);
