<?php
session_start();

require_once "common.php";
// Retrieve the token from the session so we can verify the server's
// response.
$token = $_SESSION['openid_token'];

// Complete the authentication process using the server's response.
list($status, $info) = $consumer->completeAuth($token, $_GET);

$openid = null;

// React to the server's response.  $info is the OpenID that was
// tried.
if ($status != Auth_OpenID_SUCCESS) {
    
    $msg = sprintf("Verification of %s failed.", $info);
} else {
    if ($info) {
        // This means the authentication succeeded.
        $openid = $info;
        $esc_identity = htmlspecialchars($openid, ENT_QUOTES);
        $_SESSION['flux_openid_verified'] = true;
        $success = sprintf('You have successfully verified ' .
                           '<a href="%s">%s</a> as your identity.',
                           $esc_identity,
                           $esc_identity
                           );
    } else {
        // This means the authentication was cancelled.
        $_SESSION['flux_openid_verified'] = false;
        unset($_SESSION['flux_openid_url']);
        $msg = 'Verification cancelled.';
    }
} 
if ($msg) {
    print $msg;
    die();
}
$tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
$query = "select comment_author, comment_author_email, comment_author_url from ".$tablePrefix."blogcomments where comment_author_url = ".$GLOBALS['POOL']->db->quote($_SESSION['flux_openid_url'])." order by id DESC LIMIT 1";
$res = $GLOBALS['POOL']->db->query($query);
$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);

?>
<html>
<head>
<script type="text/javascript">
if (opener) {
    
    opener.openIdOk(<?php echo "'".$row['comment_author']."','". $row['comment_author_email']."'";?>);
    window.close();
    
} else {
    parent.openIdOk(<?php echo "'".$row['comment_author']."','". $row['comment_author_email']."'";?>);    
}

</script>
</head>
<body>
Authentication succeeded.
</body>
</html>
