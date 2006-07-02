<?php
include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

header("Content-type: text/xml");
?>
<span>
<strong><a href="<? print $_POST['uri']; ?>"><? print $_POST['name'] ?></a> @ now</strong>
<br/>
<?php print bx_helpers_string::makeLinksClickable(bx_plugins_blog_handlecomment::cleanUpComment($_POST['text'])); ?>
</span>

