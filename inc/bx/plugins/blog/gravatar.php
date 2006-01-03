<?php

class bx_plugins_blog_gravatar {
    
    static function getLink($email, $size = 80, $color = "000000") {
        $grav_url = 'gravatar_id='.md5($email).
        "&border=$color".
        "&rating=X".
        "&size=".$size;
        $grav_url = BX_WEBROOT.'dynimages/gravatar/'.md5($grav_url).'?'.$grav_url;
        
        return $grav_url;
    }
}
?>
