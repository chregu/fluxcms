<?php


class bx_notificationmanager {
    
    static protected $instance = array();
    
    protected function __construct($type) {
        $this->transport = call_user_func(array("bx_notifications_".$type,"getInstance"));
    }
    
    static public function getInstance($type = "mail") {
        if (!isset(self::$instance[$type])) {
            self::$instance[$type] = new bx_notificationmanager($type);
        } 
        return self::$instance[$type];
        
    }
    public function send( $to, $subject, $message, $fromAdress, $fromName= null, $options = array()) {
        
        $this->transport->send($to,$subject,$message, $fromAdress, $fromName,$options);    
    }
    
    public function sendByUsername( $to, $subject, $message, $fromAdress, $fromName= null, $options = array()) {
        
        $this->transport->sendByUsername($to,$subject,$message, $fromAdress, $fromName, $options);    
    }

    
    static public function sendToDefault($username, $subject, $message, $fromAdress, $fromName = null, $options = array()) {
        if (isset($GLOBALS['POOL']->config->notifications['default'])) {
            $default = $GLOBALS['POOL']->config->notifications['default'];
        } else {
            $default = 'mail';
        }      
        
        
        $t = self::getInstance($default);
        
        $t->sendByUsername($username,$subject,$message,$fromAdress,$fromName,$options);
    }
    
    
}