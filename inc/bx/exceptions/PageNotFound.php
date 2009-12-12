<?php
class BxPageNotFoundException extends Exception {
    
    function __construct($uri) {
        $this->message = "$uri was not found.";
        parent::__construct();
    }
}
?>
