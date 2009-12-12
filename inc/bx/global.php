<?php

define ('BX_PROPERTY_DEFAULT_NAMESPACE','bx:');
define ('BX_PROPERTY_PIPELINE_NAMESPACE','bx-pipeline:');

define('BX_PARAMETER_TYPE_DEFAULT', 'default');

class bx_global {
    
    static $instance = null;
    
    private $config = null;
    static $registeredStreams = array();
    
    private function __construct() {
    }
    
    public static function getInstance () {
        if (!bx_global::$instance) {
            bx_global::$instance = new bx_global();
        } 
        return bx_global::$instance;
    }
    
    public static function getConfigInstance() {
            $c = bx_config::getInstance();
            return $c;   
    }
    
    public static function registerStream($stream) {
        if (!in_array($stream,self::$registeredStreams)) {
            include_once(BX_LIBS_DIR.'/streams/'.$stream.'.php');
            stream_wrapper_register($stream, "bx_streams_".$stream);
            self::$registeredStreams[] = $stream;
        }
    }
}