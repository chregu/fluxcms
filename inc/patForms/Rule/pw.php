<?php

class patForms_Rule_pw extends patForms_Rule_Retype {

	var	$validatorErrorCodes  =   array(
		"C"	=>	array(
			1	=>	"Die Passwörter stimmen nicht überein.",
		),

	);
        function initFormRule($filter,$ruleNode) {

        	$this->_fieldNames = array('private_password', 'nosave_password2');
        	$this->_conditions['private_password'] = 'pw';

        }
        
        
        
        
}
?>
