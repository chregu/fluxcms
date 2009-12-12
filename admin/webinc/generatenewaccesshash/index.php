<?php

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");


?>
<html>

<head><title>Flux CMS Generate new key</title>
<link href="<?php echo BX_WEBROOT;?>themes/standard/admin/css/overview.css" type="text/css" rel="stylesheet" />
</head>
<body>
<p>
<?php

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin'))) {
    print "Access denied";
    die();
}
 $h = bx_helpers_perm::updateAccessHash();
print "New key generated. Go back to your blog collection, to check your new links.<br/><br/>"; 
print '<a style="text-decoration: underline;" href="'.BX_WEBROOT_W.$_GET['path'].'rss.xml?ah='.$h.'">RSS Feed (incl. private)</a><br/>';
print '<a style="text-decoration: underline;" href="'.BX_WEBROOT_W.$_GET['path'].'latestcomments.xml?ah='.$h.'">RSS Comments (incl. private)</a><br/>';


?>
</p>
</body>
</html>

