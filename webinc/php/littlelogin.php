<?php
include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

if (!$permObj->isAllowed('/',array('admin'))) {
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Flux CMS Admin - Login</title>
<link rel="stylesheet" type="text/css"
	href="http://flux/themes/standard/admin/css/head.css" media="screen" />
<link rel="stylesheet" type="text/css"
	href="http://flux/themes/standard/admin/css/login.css" media="screen" />
<script typ="text/javascript">
function service() {
    document.forms.login.submit();
}
</script>
<style type="text/css">
<!--
label {
    width: 80px;
}
body {
    margin: 20px;
    
}
-->
</style>

</head>

<body onload="document.forms.login.username.focus();">

<?php 
if(isset($_POST['password'])) {
    
    echo '<p style="color:red;font-weight:bold">Login wrong</p>';
}
?>

<form method="post" action="/webinc/php/littlelogin.php" name="login" id="login">

    <label>User:</label> <input name="username" type="text"
    	class="input" /> 
    <br />
    <div id="pwd"><label>Password:</label> <input name="password"
    	type="password" class="input" /></div>
    <br />
    <input type="submit" value="Submit"
    	onsubmit="service();return false;" name="submitButton" /> 
    <br />

</form>


</body>
</html>
<?php
}

else {

?>
<script typ="text/javascript">
window.opener.saveContent();
self.close();
</script>
<?php 

}




