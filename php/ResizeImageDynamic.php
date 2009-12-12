<?php
include_once("../conf/config.inc.php");
$allowed_sizes = array("68,68,rect","101","102","400","342","60");
include_once("./ResizeImage.php");
$image = $_SERVER['DOCUMENT_ROOT'].$_SERVER['REDIRECT_URL'];
$image = preg_replace("/.html$/","",$image);

if (strpos( $_SERVER['REDIRECT_URL'],"/files/")) {
	$rewrite = array("dynimages/","");
} else {
	$rewrite = array("dynimages","data");
}
$bla = new ImageResize($image,$allowed_sizes,$rewrite);
?>

