<?php
class BxPageNotAllowedException extends Exception {
    
    function __construct($uri = null) {
        if (!$uri) {
            $uri = substr($_SERVER['REQUEST_URI'],1);
        }
        $uri = preg_replace("#.loginerror=1#",'',$uri);
        $this->message = "$uri is not allowed to be viewed.";
        parent::__construct();
        if (!empty($_GET) && isset($_GET['loginerror'])) {
            $this->userInfo =  $GLOBALS['POOL']->i18nadmin->translate('Wrong login or password.');
        }
    }
}
?>
