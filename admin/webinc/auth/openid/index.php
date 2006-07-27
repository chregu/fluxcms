<?php

include_once("../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../..");

$conf = bx_config::getInstance();

$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);

//define("Auth_OpenID_NO_MATH_SUPPORT",1);	// a sad, sad hack as the guy from http://svn.smallwhitecube.com/blogtastic/trunk/comments.php said but it works
$immediate = false;
include("common.php");

session_start();
// Render a default page if we got a submission without an openid
// value.
$url = $_POST['username'];
$_SESSION['flux_openid_url'] = $url;
if (!$url) {
    $error = "Expected an OpenID URL.";
    //include 'index.php';
    
    exit(0);
}
$_SESSION['flux_openid_url'] = $url;
$_SESSION['flux_openid_verified'] = false;
setcookie('openid_enabled', $url, time()+30*24*60*60, '/');
$openid = $url;
$process_url = BX_WEBROOT.'admin/webinc/auth/openid/finish_auth.php';
               

$trust_root = BX_WEBROOT;
// Begin the OpenID authentication process.

list($status, $info) = @$consumer->beginAuth($openid);
// Handle failure status return values.
if ($status != Auth_OpenID_SUCCESS) {
    $error = "Authentication error.";
    //include 'index.php';
    exit(0);
}
// Redirect the user to the OpenID server for authentication.  Store
// the token for this authentication so we can verify the response.
$_SESSION['openid_token'] = $info->token;
$redirect_url = $consumer->constructRedirect($info, $process_url, $trust_root);

//print $redirect_url;
header("Location: ".$redirect_url);
?>
