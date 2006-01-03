<?php

/* NASTY CODE: should be an admin plugin... */

include_once("../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../..");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Recover Lost Password</title>
        <link rel="stylesheet" type="text/css" href="<?php echo BX_WEBROOT;?>themes/standard/admin/css/formedit.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo BX_WEBROOT;?>themes/standard/admin/css/head.css"/>
        
    </head>
    
<body>
<div id="top">
    Flux CMS
</div>
<div id="container">


</div>
 <div id="admincontent" style="margin-left: 20px">
<h2>Recover Your Lost Password</h2>
<table class="bigUglyBorderedEditTable" width="550" >
<?php
$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permm = bx_permm::getInstance($confvars);

?>

<form action="." method="POST">
<?php
$db = $GLOBALS['POOL']->db;
$tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();


if (isset($_REQUEST['username'])) {
    $name = $_REQUEST['username'];
    
    $p = bx_permm::getInstance();
    
    $query = "select user_email, id from ".$tablePrefix."users where user_login = ". $db->quote($name);
    $row = $db->queryRow($query,null,MDB2_FETCHMODE_ASSOC);
    if(!$row) {
        print "<tr><td>There's no email adress for that user available (or user is not in our database)</td></tr>";
        print "</table>";
        print "<p>If you don't know your username or did not provide a valid email adress, we can't revover your password and you have to get in touch with your system administrator</p>";
   
    } else {
        $email = $row['user_email'];
        $id = $row['id'];
        $hash = md5($id. $email. time() . rand() . "secret...");
        $body = 'Hi
        
Your password can now be reset.
Please go to:
        
'.BX_WEBROOT.'admin/webinc/lostpw/?hash='.$hash. '

and retype a new password.

Have fun';
        
        $query = "update  ".$tablePrefix."users set user_tmphash = '".$hash."' where id = $id";
	$res = $db->query($query);	
	if ($db->isError($res) && $res->code == -19) {
		$query2 = "alter table ".$tablePrefix."users add user_tmphash varchar(32) default ''";
		$res = $db->query($query2);
		$res = $db->query($query);
	}
        mail($email,"Revocer password for ". BX_WEBROOT, $body);
        print "<tr><td>Mail sent. Check your mailbox</td></tr>";
        print "</table>";
        
    }
    
    
} else if (!isset($_REQUEST['hash'])) {
    
    getUsername();
    
} else if (isset($_REQUEST['hash'])) {
    $query = "select  id from ".$tablePrefix."users where user_tmphash = ". $db->quote($_REQUEST['hash']);
    $row = $db->queryRow($query,null,MDB2_FETCHMODE_ASSOC);
    if (!$row) {
        print "No user with that hash found...\n";
    } else {
        $id = $row['id'];
        if (isset($_REQUEST['newpassword']) && strlen($_REQUEST['newpassword']) >= 6 && $_REQUEST['newpassword'] == $_REQUEST['newpassword2']) {
            
            $query = "update  ".$tablePrefix."users set user_tmphash = '', user_pass= '". md5($_REQUEST['newpassword'])."' where id = $id";
            $db->query($query);
             print '<tr><td>';
            print "Password updated, please login now with your new password: <br/><br/>";
            print '<a href="'.BX_WEBROOT.'admin/">'.BX_WEBROOT.'admin/</a>';
            print '</td></tr>';
            print '</table>';
            
        } else {
            print '<tr><td colspan="2">';
             if (isset($_REQUEST['newpassword']) && strlen($_REQUEST['newpassword']) < 6) {
                print "<font color='red'>Passwords has to be at least 6 characters long, please retype</font><br/>";
            } 
             else if ($_REQUEST['newpassword'] != $_REQUEST['newpassword2']) {
                print "<font color='red'>Passwords do not match, please retype</font><br/>";
            } else {
                print 'Please type in your new Password';
            }
            print "</td></tr>";
            print '<input type="hidden" name="hash" value="'.$_REQUEST['hash'].'"/>';
            print '<tr><td>New password: </td><td><input type="password" name="newpassword" value=""/></td></tr>';
            print '<tr><td>Retype new passwort:</td><td><input type="password" name="newpassword2" value=""/></td></tr>';
            print '</table>';
            print "<p><input type='submit'/></p>";
        }
        
        
    }
    
    
}


?>
</form>
</div>
</body>
</head>
</html>
<?php



function getUsername() {
    print "<tr><td colspan='2'>";
    print "In order to get your password, we need your username:";
    print "</td></tr>";
    print "<tr><td>Username</td><td>";
    print "<input name='username' /></td></tr>";
   print "</table>";
   print '  <p>
                    <input type="submit" name="send" value="submit"/>
                </p>';
   print "(If you don't know your username or did not provide a valid email adress, we can't revover your password and you have to get in touch with your system administrator)";
    
}

?>


