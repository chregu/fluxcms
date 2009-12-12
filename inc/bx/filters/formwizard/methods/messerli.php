<?php

class bxfw_messerli {

    public function __construct($fw) {
        $this->parent = $fw;
    }

    public function contactPopup($emailBodyID = '') {
        $fields = $this->parent->getFields();
       
        $screenNode = $this->parent->confctxt->query("/bxco:wizard/bxco:screen[@emailTo]");
        $screenNode = $screenNode->item(0);
        
        $emailTo = $screenNode->getAttribute("emailTo"); 
        $emailFrom = $screenNode->getAttribute('emailFrom');
         
        // try to replace from and to values
        if(!empty($fields['email'])) {
            $emailFrom = $fields['email'];
        }
        $query = "select email from personen where id = ".$fields['pID'];
        $res = $GLOBALS['POOL']->db->query($query);
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
        if(!empty($row['email'])) {
            $emailTo = $row['email'];
        }
        
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

            //var_dump("this email would have gone to $emailTo");
            mail($emailTo, $emailSubject, $emailBody, $headers);
        } else {
            print "no email info found";
        }

        $_SESSION["bx_wizard"] = array();
        return TRUE;
    }

}

?>
