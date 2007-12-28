<?php

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

$prefix = $GLOBALS['POOL']->config->getTablePrefix();
header("Content-type: text/xml");
$db = $GLOBALS['POOL']->db;
if (isset($_GET['q'])) {
	$search = $_GET['q'];
} else {
	$search = "";
}

$targetRoot = !empty($_GET['blogadmin']) ? '' : 'archive/';
if (isset($_GET['root'])) {
    $targetRoot = $_GET['root'] . $targetRoot;
}

@session_start();

if (isset($_SESSION['_authsession']) && isset($_SESSION['_authsession']['registered']) && $_SESSION['_authsession']['registered'] == true) {
    $perm = 3;
    if (!empty($_GET['blogadmin'])) {
        $perm = 7;   
    }
} else {
    $perm = 1;
}

if (empty($_GET['blogid'])) {
    $blogid = 1;
} else {
    $blogid = (int) $_GET['blogid'];
}
    
if (strlen($search) > 3) {
     $res = $db->query("select post_uri, post_title from ".$prefix."blogposts as  blogposts where  post_status & $perm and blog_id = $blogid and  MATCH (post_content,post_title) AGAINST (".$db->quote( $search) .")  LIMIT 20");
     if ($res->numRows() == 0) {
      $res = $db->query("select post_uri, post_title from ".$prefix."blogposts as  blogposts where  post_status & $perm and blog_id = $blogid and  post_title like '%" . $search . "%' order by post_date DESC LIMIT 20");
     }
     
     if ($res->numRows() == 0 ) {
      $res = $db->query("select post_uri, post_title from ".$prefix."blogposts as  blogposts where  post_status & $perm and blog_id = $blogid and post_content like '%" . $search . "%' order by post_date DESC LIMIT 20");
     }
} else {
    $res = $db->query("select post_uri, post_title from ".$prefix."blogposts as  blogposts where post_status & $perm and blog_id = $blogid   and post_title like '%" . $search . "%' order by post_date DESC LIMIT 20");
    
}
$ret = "<?xml version='1.0' encoding='utf-8'  ?><ul class='LSRes'>";
//$ret .= '<div class="LSHead">result for '.$search."</div>";
if ($res->numRows() == 0) {
    $ret .= '<li class="resultRow">Nothing found (neither in title nor fulltext)</li>';
} else {
    while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC) ) {
	if  (!$db->isUtf8) {
		$row['post_title'] = utf8_encode($row['post_title']);
	}
        $ret .= '<li class="LSRow"> » <a href="'.$targetRoot.urlencode($row['post_uri']).'.html">'.(stripslashes($row['post_title'])).'</a>';
        $ret .= '</li>';
    }
}
$ret .= "</ul>";
print $ret;
?>


