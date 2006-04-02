<?php

class bx_filters_patforms_form2mail extends bx_filters_patforms_formhandler {

    public function submitFields($params, $fields) {
        $headers = '';
        $headers .= "User-Agent: Flux CMS " . BXCMS_VERSION.'/'.BXCMS_REVISION ."\r\n";

        $charset = !empty($params['charset']) ? $params['charset'] : "ISO-8859-1";
        $headers .= "Content-Type: text/plain; charset=".$charset."\r\n";
        
        $from = 'Do not reply to this address <nobody@example.org>';
        if(!empty($params['emailFromField']) && !empty($fields[$params['emailFromField']])) {
            $from = $fields[$params['emailFromField']];
        } else if(!empty($params['emailFrom'])) {
            $from = $params['emailFrom'];
        }
        
        if(strpos($from, "\n") !== FALSE or strpos($from, "\r") !== FALSE) { 
            return FALSE;
        }
        $headers .= "From: $from\r\n";

        $emailSubject = !empty($params['subjectTemplateKey']) ? $this->getText($params['subjectTemplateKey']) : '';


        if(strpos($emailSubject, "\n") !== FALSE or strpos($emailSubject, "\r") !== FALSE) { 
            return FALSE;
        }

        $emailBody = !empty($params['bodyTemplateKey']) ? $this->getText($params['bodyTemplateKey']) : '';

        // fields contain themselves as formatted string
        $fields['__allfields__'] = bx_helpers_string::formatTextFields($fields);
        
        // replace textfields in subject and body
        $emailSubject = bx_helpers_string::replaceTextFields($emailSubject, $fields);
        $emailBody = bx_helpers_string::replaceTextFields($emailBody, $fields);
        
        // recode utf8 strings
        if ($charset != "ISO-8859-1" && function_exists("iconv")) {
            $emailSubject=iconv("utf8",$charset,$emailSubject);
            $emailBody=iconv("utf8",$charset,$emailBody);
        } else {
          // decode utf8 strings
          $emailSubject = utf8_decode($emailSubject);
          $emailBody = utf8_decode($emailBody);
        }

        if(!empty($params['emailTo'])) {        
            mail($params['emailTo'], $emailSubject, $emailBody, $headers);
            return TRUE;
        }
        return FALSE;
    }
    
}

?>
