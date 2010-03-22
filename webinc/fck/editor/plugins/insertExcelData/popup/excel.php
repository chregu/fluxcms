<?php


include_once("../../../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../../../../");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin'))) {
    die("Not Allowed");
}

$excelFile = '';
$dir = BX_PROJECT_DIR."tmp/";

require_once "header.php";

if(isset($_POST['newFile']) AND isset ($_FILES['xls']) AND $_FILES['xls']['error'] == 0){
    $excelFile = $dir.$_FILES['xls']['name'];
    move_uploaded_file($_FILES['xls']['tmp_name'], $excelFile);
}



require_once 'xls2html.php';

$xls = new xls2html();


if(isset($_POST['xls2html_import'])){	
	$xls->import($_POST['xls2html_xslFilename']);
	exit();
}
else if($excelFile != ''){
	$xls->importForm($excelFile);
}
else {
?>
<br /><br /><p>Load Excel-File</p>
<form name="" action="excel.php" enctype="multipart/form-data" method="post">
<input type="file" name="xls"><br /><br />
<input type="submit" name="newFile" value="upload" />
</form>
<?php
}
require_once 'footer.php';



