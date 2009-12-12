<?php

class ImageDirectoryIterator extends DirectoryIterator {
    
    
    function __construct($path) {
        parent::__construct($path);
    }
    
    
    public function isImage() {
       $fn = $this->getFileName();
       if ($fn != ".." && $fn != ".") {
               return bx_helpers_image::isImage($this->getFileName());
       } 
       return false;
    }
}
