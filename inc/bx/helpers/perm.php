<?php

class bx_helpers_perm {

    static function getUsername() {
        $perm = bx_permm::getInstance();
        return $perm->getUsername();
    }
}

?>
