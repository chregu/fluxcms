<?php

class bx_dynimage_filters_gd_grayscale extends bx_dynimage_filters_gd {
    
    public function start($imgIn) {
        
        imagefilter($imgIn, IMG_FILTER_GRAYSCALE);
        
        return $imgIn;
        
    }
    
}
