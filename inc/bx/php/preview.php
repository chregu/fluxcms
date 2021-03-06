<?php
include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

header("Content-type: text/xml");


$xslt = new xsltprocessor();
$xml = file_get_contents(BX_LIBS_DIR.'plugins/blog/preview.xml');
$_POST['text'] = bx_helpers_string::makeLinksClickable(bx_plugins_blog_handlecomment::cleanUpComment($_POST['text']));

$xmls = str_replace(array("{{mail}}","{{name}}","{{text}}","{{uri}}"),array($_POST['mail'],$_POST['name'],$_POST['text'],$_POST['uri']),$xml);
$xml = new domdocument();
$xml->loadXML($xmls);

$xsls = file_get_contents(BX_LIBS_DIR.'plugins/blog/preview.xsl');


$xsls = str_replace('{{blog.xsl}}',BX_THEMES_DIR.$GLOBALS['POOL']->config->theme. '/blog.xsl',$xsls);

$xsl = new domdocument();
$xsl->loadXML($xsls);
$xslt->importStylesheet($xsl);

$xslt->registerPHPFunctions();
print ( $xslt->transformToXML($xml));

?>

