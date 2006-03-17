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
    
    public function send($to, $subject, $message, $fromAdress = null, $fromName= null) {
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
        $headers = "From: $from\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
        $headers .= "User-Agent: Flux CMS Mailer (".BXCMS_VERSION."/".BXCMS_REVISION.")"; 
        if (function_exists('iconv')) {
            $subject = iconv("UTF-8","ISO-8859-15",$subject); 
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
     $to = $row['user_fullname'] . ' <' .$row['user_email'].'>'; 
     
     if ($to) {
         $this->send($to,$subject,$message,$fromAdress, $fromName);
     }
    }
    
}