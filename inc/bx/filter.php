<?php

class bx_filter extends bx_component {

    private $headers = array();
    
    protected function __construct($mode) {
        $this->mode = $mode;
    }

    public function DOMStart(&$dom, $position , $collUri = NULL, $filename = NULL) {
        if(method_exists($this, $position)) {
            $this->$position($dom, $collUri, $filename);
        }
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
    }
    
    public function getHeaders() {
        return $this->headers;
    }
                               
    public function hasHeaders() {
        return !empty($this->headers);
    }
}

?>
