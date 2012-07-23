<?php

require_once BX_INCLUDE_DIR.'/Recaptcha/recaptchalib.php';

class patForms_Element_Recaptcha extends patForms_Element_String {

    /**
     * Stores the name of the element - this is used mainly by the patForms
     * error management and should be set in every element class.
     * @access	public
     */
    public $elementName = 'Recaptcha';

    function validateElement($value) {

        $resp = recaptcha_check_answer ($GLOBALS['POOL']->config->recaptchaprivkey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        return $resp->is_valid;
    }

    function serializeHtmlDefault($value) {
        if (!isset($GLOBALS['POOL']->config->recaptchaprivkey) || !isset($GLOBALS['POOL']->config->recaptchapubkey)) {
            
            return '<p>error: set both recaptchaprivkey and recaptchapubkey in conf/config.xml</p>';
        } else {
            
            return recaptcha_get_html($GLOBALS['POOL']->config->recaptchapubkey);
        }
    }

}
?>
