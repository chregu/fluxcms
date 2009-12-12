<?php
Interface bx_versioning_interface {
    public static function getInstance($opts);                          
            
    public function commit($rpath, $path, $log='');
    
    
    /**
    * initializes versioning and setup prerequisites
    * @acess    public
    * @return   void|false
    */
    public function init();
	
}