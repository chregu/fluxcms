<?php
//first check oldschool config.inc.php
// if that doesn't exist, do new xml-based one
// the if line can be removed later...
include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");
$conf = bx_config::getInstance();
    
 $confvars = $conf->getConfProperty('permm');
 $permObj = bx_permm::getInstance($confvars);
 if (!$permObj->isAllowed('/',array('admin', 'edit'))) {
     
     print "Access denied";
     die();
 }
header("Content-Type: text/html; charset=utf-8");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Header</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" type="text/css" href="<?php echo BX_WEBROOT;?>themes/standard/admin/css/head.css"/>
<script language="javascript">



function changeElement ()
{
if (document.forms['bookmarks'].selectElement.selectedIndex > 0) {
var element = document.forms['bookmarks'].selectElement.options[document.forms['bookmarks'].selectElement.selectedIndex].value;
if(element.match(/\//)) {
    parent.edit.location.href="<?php echo BX_WEBROOT; ?>"+element+"/";
} else {
    parent.edit.location.href="<?php echo BX_WEBROOT; ?>forms/"+element+"/";
}
document.forms['bookmarks'].selectElement.selectedIndex = 0;
}
}

</script>

</head>
<body>

<form name="bookmarks">
<div id="top"><p class="logo"><a class="logolink" href="http://flux-cms.org" target="_blank">Flux CMS</a></p>
<a class="logolink" target="_top" href="<?php echo BX_WEBROOT; ?>"><?php print bx_helpers_config::getOption('sitename');?></a>
<br class="clr"/></div>

<div id="container">
<?php
$i18n = $GLOBALS['POOL']->i18nadmin;
// this is ugly, but anyone's welcome to fix it ;)
if (file_exists("../../structure/quicklinks.php")) {
   include_once("../../structure/quicklinks.php");
?>

<span style="float: right; ">
Bookmarks:<select name="selectElement" onchange="javascript:changeElement()">
<option value="">--</option>

<?php

foreach ($quicklinks as $value => $name) {
    if($name == '') {
        print '<option value="" disabled="disabled">'.$value.'</option>';
    } else {
        print '<option value="'.$value.'">'.$name.'</option>';
    }
}
?>
</select>

</span>

<?php }




echo '<span class="logout">'.$i18n->translate('User:').''; 
  
    
    @session_start();

    print $permObj->getUsername();
 echo '
 &nbsp;
<a href="#" onClick="parent.edit.location.href=\''.BX_WEBROOT.'admin/overview/\'">'.$i18n->translate('home').'</a>
<a href="http://docs.bitflux.org/en/start/" target="help">'.$i18n->translate('help').'</a>

<a href="#" onClick="parent.location.href=\''.BX_WEBROOT.'admin/?logout\'">'.$i18n->translate('logout').'</a>
';
?>
</span>
<?php if(popoon_classes_browser::isSafari()) { ?>
<div class="browserWarning">Safari works quite well with the Flux CMS Admin. But, if you want to use any of the WYSIWYG editors, we highly recommend using a Mozilla based browser.</div>
<?php } else if(popoon_classes_browser::isOpera8()) { ?>
<div class="browserWarning">Opera 8 works quite well with the Flux CMS Admin. But, if you want to use any of the WYSIWYG editors, we highly recommend using a Mozilla based browser.</div>
<?php } else if(popoon_classes_browser::isKonqueror34()) { ?>
<div class="browserWarning">Konqueror works quite well with the Flux CMS Admin. But, if you want to use any of the WYSIWYG editors, we highly recommend using a Mozilla based browser.</div>
<?php } else if(popoon_classes_browser::isMSIEWin()) {?>
<div class="browserWarning">Not all features and esp. WYSIWYG editors are supported on MSIE/Win. <br/>We highly recommend using a Mozilla based browser.</div>
<?php } else if(!popoon_classes_browser::isMozilla()) { ?>
<div class="browserWarningRed">Your browser is not supported in  Flux CMS Admin. <br/>We highly recommend using a Mozilla based browser.</div>
<?php } else {
   /* print "Version : ";
    print BXCMS_VERSION."/";
    print BXCMS_REVISION;*/
}
    
    
    ?>
</div>
</form>
</body>
</html>
