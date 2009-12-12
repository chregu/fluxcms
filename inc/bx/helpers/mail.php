<?php

//DEPRECATED!!!!

/* use instead

 bx_notificationmanager->sendToDefault($emailTo,$emailSubject, $emailBody,$emailFrom);
*/
class bx_helpers_mail {

    static function sendmail($to, $subject, $body, $headers) {
        $hdrs = '';
        foreach($headers as $name => $value) {
            $hdrs.= "$name: $value".PHP_EOL;
        }
        
        $hdrs .= "X-Mailer: bxcmsng";
        mail($to, $subject, $body, $hdrs);
    }
}    
?>
