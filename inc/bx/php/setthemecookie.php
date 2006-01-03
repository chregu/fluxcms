<?php
$webroot = $_SERVER['HTTP_HOST'];
if (isset($_GET['theme']) and isset($_GET['themecss'])) {
    setcookie("bx_theme",$_GET['theme'],time() + 3600*24*60,"/");
    setcookie("bx_themecss",$_GET['themecss'],time() + 3600*24*60,"/");
    $_COOKIE['bx_theme'] = $_GET['theme'];
    $_COOKIE['bx_themecss'] = $_GET['themecss'];
    header("location:".$_GET['path']."");
}
?>