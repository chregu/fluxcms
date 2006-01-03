<?php
include_once("Log.php");

class bx_log {
    
    static $logInstance = NULL;
    
    public static function log($msg, $prio = PEAR_LOG_INFO) {

        if(defined('BX_LOG_FILENAME') && BX_LOG_FILENAME !== '') {
            $logFileName = trim(BX_LOG_FILENAME);
            if(!isset(bx_log::$logInstance)) {
                if (substr($logFileName,0,1) == "/") {
                    bx_log::$logInstance = &Log::singleton('file', $logFileName, 'bxcmsng', array(), LOG_INFO);
                } else {
                	bx_log::$logInstance = &Log::singleton('file', trim(BX_LOG_DIR).'/'.$logFileName, 'bxcmsng', array(), LOG_INFO);
                }
            }
            
            if(isset(bx_log::$logInstance) && is_object(bx_log::$logInstance)) {
                $bt = debug_backtrace(); 
                $ctx = str_replace(BX_PROJECT_DIR, '', $bt[0]['file']).':'.$bt[0]['line'];
                bx_log::$logInstance->log($msg, $prio, $ctx);
                return TRUE;
            }
        }

        return FALSE;
    }
    
}
