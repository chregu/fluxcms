<?php
include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

$tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();

$rating .= file_get_contents("php://input");

$ratings = explode('-',$rating);

$username = bx_helpers_perm::getUsername();

/*ajax post data
*
*	$ratings['0'] -> rating
*	$ratings['1'] -> blogid
*	$ratings['2'] -> postid
*
*/

$checkquery = "select rating from ".$tablePrefix."blograting where username = '".$username."' and postid = '".$ratings['2']."'";
$res = $GLOBALS['POOL']->db->query($checkquery);
$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

if($row) {
	//update old rating
	$updatequery = "update ".$tablePrefix."blograting set rating = '".$ratings['0']."', blogid = '".$ratings['1']."', postid = '".$ratings['2']."' where username = '".$username."' and postid = '".$ratings['2']."'";
	$GLOBALS['POOL']->db->query($updatequery);
} else {
	$query = "insert into ".$tablePrefix."blograting (rating, blogid, postid, username) value('".$ratings['0']."', '".$ratings['1']."', '".$ratings['2']."', '".$username."')";
	$res = $GLOBALS['POOL']->db->query($query);
}




?>
