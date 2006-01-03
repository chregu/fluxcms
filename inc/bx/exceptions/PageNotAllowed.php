<?php
class BxPageNotAllowedException extends Exception {
    
    function __construct($uri = null) {
        if (!$uri) {
            $uri = substr($_SERVER['REQUEST_URI'],1);
        }
        $this->message = "$uri is not allowed to be viewed.";
        parent::__construct();
    }
}
?>
