<?php

class bx_helpers_color {

    /**
     *  Converts a hex triple into an rgb-array. 
     *
     *  @param  string $hex 
     *  @access public
     *  @return array
     */
    static function Hex2Rgb($hex) {
        $color = hexdec($hex);
        return array(($color >> 16) & 255, ($color >> 8) & 255, $color & 255);
    }

}

?>
