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
    
    public function send($to, $subject, $message, $fromAdress = null, $fromName= null,$options = array()) {
        if (!$fromAdress) {
            $fromAdress = 'unknown@example.org';   
        }
        
        if ($fromName) {
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
        
        
        $headers = "From: $from\r\n";
        $headers .= "User-Agent: Flux CMS Mailer (".BXCMS_VERSION."/".BXCMS_REVISION.")\r\n"; 
        
        if (empty($options['charset'])) {
            $options['charset'] = 'UTF-8';
        }
        
        $headers .= "Content-Type: text/plain; charset=".$options['charset']."\r\nContent-Transfer-Encoding: 8bit\r\n";
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
        mail($to, $subject, $message, $headers);
        return true;
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
     $to = $row['user_fullname'] . ' <' .$row['user_email'].'>';
     if ($to) {
         $this->send($to,$subject,$message,$fromAdress, $fromName);
     }
    }
    
}