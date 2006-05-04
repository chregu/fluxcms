<?php

class patForms_Rule_email extends patForms_Rule_Retype {

	var	$validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"Die Email-Adressen stimmen nicht überein.",
		),

	);

        function initFormRule($filter,$ruleNode) {

        	$this->_fieldNames = array('private_email', 'private_email2');
        	$this->_conditions['private_email'] = 'email';

        }
        
        
        
        
}
?>