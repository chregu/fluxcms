<?php

class ImageDirectoryIterator extends DirectoryIterator {
    
    
    function __construct($path) {
        parent::__construct($path);
    }
    
    
    public function isImage() {
        return bx_helpers_image::isImage($this->getFileName());
    }
}
