<?php

class bxfw_bhsKontakt {
    
    private $forms = array('jahresbericht', 'leitbild', 'lsvformular', 'ziitig', 'ezscheine', 'comments');
    
    public function __construct($fw) {
        $this->parent = $fw;
    }
    
    function emailFields($emailBodyID = '') {
        
        $fields = $this->parent->getFields();
        $isok = false;
        
        foreach($this->forms as $formname) {
            if (isset($fields[$formname]) && !empty($fields[$formname])) {
                $isok=true;
                break;
            }
        }
        
        if ($isok === false) {
            return false;
        } 
        
        $screenNode = $this->parent->confctxt->query("/bxco:wizard/bxco:screen[@emailTo]");
        $screenNode = $screenNode->item(0);
        
        $emailTo = $screenNode->getAttribute("emailTo"); 
        $emailFrom = $screenNode->getAttribute('emailFrom');
         
        if ($emailTo) {
            $emailSubject = $screenNode->getAttribute("emailSubject");

            $bodyID = $screenNode->getAttribute('emailBodyID');
            
            if(!empty($bodyID)) {
                $emailBodyID = $bodyID;
            }
            
            if(!empty($emailBodyID)) {
                $emailBody = utf8_decode($this->parent->lookup($emailBodyID));
                $this->parent->_replaceTextFields($emailBody, $fields);
            } else {
                $emailBody = "";
                foreach ($fields as $key => $value) {
                    $emailBody .= "$key: $value\n";
                }
            }
            
            $headers = '';
            
            if(!empty($emailFrom)) {
                $headers .= "From: $emailFrom\r\n";
            }

            mail($emailTo, $emailSubject, $emailBody, $headers);
        } else {
            print "no email info found";
        }

        $_SESSION["bx_wizard"] = array();
        return TRUE;
    }

}

?>
