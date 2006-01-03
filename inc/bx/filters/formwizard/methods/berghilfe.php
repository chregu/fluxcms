<?php

class bxfw_berghilfe {

    protected $dbfields = array ("variabel_text","betrag","ueberweisung","anrede","company","firstname","birthday","name","address","pobox","zip","city","country","email","phone","comments"); 
    
    
    public function __construct($fw) {
        $this->parent = $fw;
    }
    
    public function yellowworldNPO() {
        $fields = $this->parent->getFields();
        
        
        if ($this->dbfields) {
            $keys = implode(",",$this->dbfields);
            $values = "";
            foreach($this->dbfields as $value) {
                $values .= "'".mysql_escape_string($fields[$value])."',";
                
            }
            $keys .= ",lang";
            $values .= "'".$this->parent->lang."'"; 
            $query = "insert into spenden ($keys) VALUES ($values)";
            $res = $GLOBALS['POOL']->db->query($query);
        }
        
        if($fields['ueberweisung'] == 'kreditkarte') {
            $this->parent->emailFields('emailBodyOnlineSpendeKreditkarte');
            $yellowValues = array(
                'txtMallkey' => 'berghilfe_s',
                'txtDonpurp' => $this->parent->lookup('txtDonpurp'),
                'txtESR_Member' => $this->parent->lookup('txtESR_Member'),
                'txtESR_Ref' => '',//$this->parent->lookup('txtESR_Ref'),
                'txtTotal' => $fields['betrag'],
                'txtExtraInfo' => $fields['comments'],
                // 'txtCompany' => $fields['company'],  
                'txtTitle' => $this->parent->lookup($fields['anrede']),
                'txtFirstname' => $fields['firstname'],
                'txtName' => $fields['name'],
                'txtStreet1' => $fields['address'],
                'txtStreet2' => '',
                'txtPobox' => $fields['pobox'],
                'txtZipCode' => $fields['zip'],
                'txtCity' => $fields['city'],
                'txtEMail' => $fields['email'],
                'txtExtraInfo' => $fields['comments'],
                'txtLangcode' => $this->parent->lang == 'fr' ? 4108 : 2055
            );
           
            foreach($yellowValues as $key => $val) {
                $yellowValues[$key] = $val;
            }
            
            
            $yellowQuery = http_build_query($yellowValues);
            
            $this->parent->setHeader('Location', $this->parent->getParameter($this->parent->getCurrentRequest('collUri'), 'targetURL').'?'.$yellowQuery);
            
        } else {
            $this->parent->emailFields('emailBodyOnlineSpende');
            $redirect = $this->parent->getParameter($this->parent->getCurrentRequest('collUri'), 'redirectAfterEmail');
            if(!empty($redirect)) {
                $this->parent->setHeader('Location', $redirect);
            }
        }
        
        $_SESSION["bx_wizard"] = array();

    }

}

?>
