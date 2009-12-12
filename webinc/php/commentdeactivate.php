<?php
include_once("../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../..");

        $tablePrefix =  $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select comment_author_email, comment_posts_id from ".$tablePrefix
        ."blogcomments where comment_notification_hash = '".$_GET['id']."'";
        print "<pre/>";
        $res = $GLOBALS['POOL']->db->query($query);
        $re = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        $queryupdate = "update ".$tablePrefix."blogcomments set comment_notification = '0' "
        ."where comment_author_email = '".$re['comment_author_email']."' and comment_posts_id = '".$re['comment_posts_id']."'";
        $var=$GLOBALS['POOL']->db->query($queryupdate);
        print "<p align='center'><b>Notice</b></p>";
        print "<p align='center'>You will not longer receive mails for this post.</p>";
?>
