<?php


class bx_notifications_mail extends bx_notification {

    static protected $instance = null;

    protected function __construct() {

    }

    static public function getInstance() {
        if (!(self::$instance)) {
            self::$instance = new bx_notifications_mail();
        }
        return self::$instance;
    }

    public function send($to, $subject, $message, $fromAdress = null, $fromName= null, $options = array()) {
        if (!$fromAdress) {
            $fromAdress = 'unknown@example.org';
        }

        if ($fromName && PHP_OS != 'WINNT') {
            $from = $fromName . '<'.$fromAdress.'>';
        } else {
            $from = $fromAdress;
        }

        if(strpos($from, "\n") !== FALSE or strpos($from, "\r") !== FALSE) {
            throw new Exception("From: is invalid.");
        }
        if(strpos($to, "\n") !== FALSE or strpos($to, "\r") !== FALSE) {
            throw new Exception("To: is invalid.");
        }

        if(strpos($subject, "\n") !== FALSE or strpos($subject, "\r") !== FALSE) {
            throw new Exception("Subject: is invalid.");
        }

        if (!defined('PHP_EOL')) {
            define('PHP_EOL',"\n");
        }
        $headers = "From: $from".PHP_EOL;
        $headers .= "User-Agent: Flux CMS Mailer (".BXCMS_VERSION."/".BXCMS_REVISION.")".PHP_EOL;
        if (!empty($_SERVER['HTTP_HOST'])) {
            $headers .= "X-Flux-Host: ".$_SERVER['HTTP_HOST'].PHP_EOL;
        }
        if (empty($options['charset'])) {
            $options['charset'] = 'UTF-8';
        }
        else if ($options['charset'] == "utf8") {
            $options['charset']  = 'UTF-8';
        }

        if (!empty($options['bcc'])) {
        	$headers .= "Bcc: ". $options['bcc'].PHP_EOL;
        } 
        
        $headers .= "Content-Type: text/plain; charset=\"".$options['charset']."\"".PHP_EOL."Content-Transfer-Encoding: 8bit".PHP_EOL;
        // recode utf8 strings
        if ($options['charset'] != "UTF-8") {
         if (function_exists("iconv")) {
            $subject=iconv("utf8",$options['charset'],$subject);
            $message=iconv("utf8",$options['charset'],$message);
         } else {
          // decode utf8 strings
          $subject = utf8_decode($subject);
          $message = utf8_decode($message);
         }
        }

        //make correct 7bit header for the subject
        $subject = preg_replace('~([\xA0-\xFF])~e', '"=" . strtoupper(dechex(ord("$1")))', $subject);
        $subject = '=?'.$options['charset'].'?Q?' . $subject . '?=';

        return mail($to, $subject, $message, $headers);
    }

    public function sendByUsername($username, $subject, $message, $fromAdress = null, $fromName = null) {

        $prefix = $GLOBALS['POOL']->config->getTablePrefix();

        $query = "select user_email,user_fullname from ".$prefix."users where user_login =".$GLOBALS['POOL']->db->quote($username);

        $row = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (MDB2::isError($row)) {
            throw new PopoonDBException($row);
        }
        if (!$row['user_fullname']) {
            $row['user_fullname'] = $username;
        }

        if (!empty($row['user_email'])) {


            $to = $row['user_fullname'] . ' <' .$row['user_email'].'>';
            if ($to) {
                $this->send($to,$subject,$message,$fromAdress, $fromName);
            }
        }
    }

}
