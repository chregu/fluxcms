<?php

require_once "header.php";

error_reporting(2047);


if(isset($_POST['newFile']) AND isset ($_FILES['xls']) AND $_FILES['xls']['error'] == 0){
    copy ($_FILES['xls']['tmp_name'],'test.xls');
}



require_once('xls2html.php');

$xls = new xls2html();


if(isset($_POST['xls2html_import'])){	
	$xls->import('test.xls');
	exit();
}
else{
	$xls->importForm('test.xls');
}

?>
<br /><br /><p>Neues Excel-File laden</p>
<form name="" action="excel.php" enctype="multipart/form-data" method="post">
<input type="file" name="xls"><br /><br />
<input type="submit" name="newFile" value="upload" onclick="return sicher();" />

</form>



<?php




?>

