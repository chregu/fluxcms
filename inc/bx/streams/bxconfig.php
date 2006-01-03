<?php

class bx_streams_bxconfig {
    
    private $html;
    
    private $position = 0;
    
    function stream_open ($path, $mode, $options, &$opened_path) {
        $path = $this->getPath($path);
        $this->fd = fopen($path, $mode, $options);
        if(!$this->fd) {
            return false;
        } 
        return true;
    }
    
    function getPath($path) {
        //without dummy, parse_url reports errors in root dir 
        $path = preg_replace("#^[a-zA-Z0-9]*:/+#","/",$path);
        // 2 returns configfile path
        $path = bx_collections::getCollectionUri($path, 2) ;
        return  $path;
    }
    
    
    function stream_read($count) {
        
        return fread($this->fd,$count);
    }
    
    function stream_write($data) {
        return fwrite($this->fd, $data);
    }
    
    
    function stream_tell() {
        return ftell($this->fd);
    }
    
    function stream_eof() {
        return feof($this->fd);
    }
    
    function stream_seek($offset, $whence) {
        return fseek($this->fd,$offset,$whence);
    }
    
    function stream_close() {
        return fclose($this->fd);
    }
    
    function url_stat($url) {
        
        $path = $this->getPath($url);
        if(!file_exists($path)) {
            return FALSE;
        }
        return  stat($path);
    }
    function stream_stat() {
        return fstat($this->fd);
    }
}



?>
