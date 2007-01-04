<html>

<head><title>Flux CMS Comment Approval</title>
</head>
<body>
<?php


include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");


$conf = bx_config::getInstance();
$db = $GLOBALS['POOL']->db;
$tablePrefix = $conf->getTablePrefix();

if (!isset($_GET['hash'])) {
    die ("No hash given");
}

if (!preg_match("#[0-9a-fr]{33}#",$_GET['hash'])) {
    die ("Invalid hash");
}

$query = "select id, comment_author, comment_hash from ".$tablePrefix."blogcomments where comment_hash = ".$db->quote($_GET['hash']); 

$row = $db->queryRow($query,null,MDB2_FETCHMODE_ASSOC);
if (!$row) {
    die("No comment with that hash found (or you already used this link before)");
}
$prefix = substr($row['comment_hash'], 0,1);

$hash = substr($row['comment_hash'], 1);
             
if ($prefix == "r") {
    $query = "update ".$tablePrefix."blogcomments set comment_status = 2, comment_hash = '' where id = " .$row['id'];
    $db->query($query);
    $url = 'http://rest.flux-cms.org/1.1/report-spam?hash='.md5($hash);
    print "Comment by " . $row['comment_author'] . ' set to moderated.<p/>';
} else if ($prefix == "a") {
    $query = "update ".$tablePrefix."blogcomments set comment_status = 1, comment_hash = '' where id = " .$row['id'];
    $db->query($query);
    $url = 'http://rest.flux-cms.org/1.1/report-ham?hash='.md5($hash);
    print "Comment by " . $row['comment_author'] . ' set to approved.<p/>';
} else {
    die("Invalid Hash");
}

print "If you want to edit the comment, go to <br/>";
print '<a href="'.BX_WEBROOT.'admin/edit/blog/sub/comments/?id='.$row['id'].'">'.BX_WEBROOT.'admin/edit/blog/sub/comments/?id='.$row['id'].'</a>';

$req = new HTTP_Request($url,array("timeout" => 5));
    $req->sendRequest();



?>
</body>
</html>

