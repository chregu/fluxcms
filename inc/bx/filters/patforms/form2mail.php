<?php

class bx_filters_patforms_form2mail extends bx_filters_patforms_formhandler {

    public function submitFields($params, $fields) {

        
        $options = array();
        $options['charset'] = !empty($params['charset']) ? $params['charset'] : "UTF-8";

        $from = 'Do not reply to this address <nobody@example.org>';
        if (!empty($params['emailFromField']) && !empty($fields[$params['emailFromField']])) {
            $from = $fields[$params['emailFromField']];
        } else if (!empty($params['emailFrom'])) {
            $from = $params['emailFrom'];
        }

        if (strpos($from, "\n") !== FALSE or strpos($from, "\r") !== FALSE) {
            return FALSE;
        }
        
     
        $to = array();
        if (!empty($params['emailToField']) && !empty($fields[$params['emailToField']])) {
            $toField = $fields[$params['emailToField']];
            # only spammers use newlines
            if (strpos($toField, "\n") !== FALSE or strpos($toField, "\r") !== FALSE) {
                return FALSE;
            }
            # not more than 5 rcpt's per request
            else if( substr_count($toField, "," ) > 4) {
                return FALSE;
            }
            # at least one @ please
            else if( substr_count($toField, "@" ) == 0) {
                $toField = '';
            }
            else {
                $to[] = $toField;
            }
        } 

        if (!empty($params['emailTo'])) {
            $to[] = $params['emailTo'];
        }       
        $to = implode(',', $to);

        // option for bcc
        if (!empty($params['emailBcc'])) {
            $options['bcc'] = $params['emailBcc'];
        }



        $emailSubject = !empty($params['subjectTemplateKey']) ? $this->getText($params['subjectTemplateKey']) : '';

        if (!$emailSubject) {
            if ((!empty($params['subjectField']) && !empty($fields[$params['subjectField']]))) {
                $emailSubject = $fields[$params['subjectField']];
            }
        }

        if (strpos($emailSubject, "\n") !== FALSE or strpos($emailSubject, "\r") !== FALSE) {
            return FALSE;
        }

        $emailBody = !empty($params['bodyTemplateKey']) ? $this->getText($params['bodyTemplateKey']) : '';

        // fields contain themselves as formatted string
        $fields['__allfields__'] = bx_helpers_string::formatTextFields($fields);

        // replace textfields in subject and body
        $emailSubject = bx_helpers_string::replaceTextFields($emailSubject, $fields);
        $emailBody = bx_helpers_string::replaceTextFields($emailBody, $fields);

        if (!empty($to)) {
            $n = bx_notificationmanager::getInstance("mail");
            return $n->send($to, $emailSubject, $emailBody, $from, null, $options);
        }
        return FALSE;
    }

}
