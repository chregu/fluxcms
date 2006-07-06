<?php
        
class bx_filters_form2mail extends bx_filter {
    
    static private $instance = array();
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_filters_form2mail($mode);
        } 
        return self::$instance;
    }   
    
    public function postHTML(&$dom) {
        $params = $this->getParameterAll();

        if(!empty($params['emailTo']) && !empty($_POST['form2mail'])) {
            $body = '';
            
            foreach($_POST['form2mail'] as $key => $value) {
                if($key != 'submit') {
                    $body .= "$key:\n$value\n\n";
                }
            }

            $subject = !empty($params['subject']) ? $params['subject'] : 'form2mail';
            $to = $params['emailTo'];
            $from = !empty($params['emailFrom']) ? $params['emailFrom'] : $params['emailTo'];
            
            $nm = bx_notificationmanager::getInstance("mail");
            $nm->send($to, $subject, $body, $from, null, array('charset'=>'UTF-8')); 
            
        }
    }
}

?>
