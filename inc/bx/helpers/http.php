<?php

class bx_helpers_http {

    static function redirect($url) {
	header("Location: $url");
    }
}

?>
