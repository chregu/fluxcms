<?php

class bx_filters_patforms_form2mail extends bx_filters_patforms_formhandler {

    public function submitFields($params, $fields) {
      
        
        $options = array();
        $options['charset'] = !empty($params['charset']) ? $params['charset'] : "UTF-8";
        
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

        if(!empty($params['emailTo'])) {   
            $n = bx_notificationmanager::getInstance("mail");
            return $n->send($params['emailTo'],$emailSubject, $emailBody, $from, null,$options);
        }
        return FALSE;
    }
    
}
