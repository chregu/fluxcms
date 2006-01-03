<?php
include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

include_once("./ResizeImage.php");
$image = BX_OPEN_BASEDIR.substr($_SERVER['REDIRECT_URL'],strpos($_SERVER['REDIRECT_URL'],"/dynimages"));

$image = preg_replace("/\.html$/","",$image);
$image = preg_replace("/\/{2,}/","/",$image);
if (strpos( $_SERVER['REDIRECT_URL'],"/images/")) {
	$rewrite = array(BX_OPEN_BASEDIR."dynimages/",BX_OPEN_BASEDIR);
} else if (strpos( $_SERVER['REDIRECT_URL'],"/files/")) {
	$rewrite = array(BX_OPEN_BASEDIR."dynimages/",BX_OPEN_BASEDIR);
} else {
	$rewrite = array(BX_OPEN_BASEDIR."dynimages",BX_DATA_DIR);
}
$bla = new ImageResize($image,$GLOBALS['POOL']->config['image_allowed_sizes'] ,$rewrite);
?>

