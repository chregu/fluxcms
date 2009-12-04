<?php

class popoon_helpers_image {
    
    static function getWidth($src) {
	    preg_match("|^http|",$src,$t);
	    if(!$t)
 	    $src = substr($src, 1);
        list($width) = getimagesize($src);
        return $width;
    }

    static function getHeight($src) {
	     preg_match("|^/|",$src,$t);
	    if($t)
	$src = substr($src, 1);
        list($width, $height) = getimagesize($src);
        return $height;
    }
    

        
}

