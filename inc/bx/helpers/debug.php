<?php

class bx_helpers_debug {
    static public $incFiles;
    
    static function dump_backtrace($full = false) {
        
        
        $bt = array_reverse(debug_backtrace());
        array_pop($bt);
        
        
        ?>
        <font size='1' xmlns='http://www.w3.org/1999/xhtml'><table border='1' cellspacing='0'>
        <tr><th bgcolor='#7777dd' colspan='3'>Call Stack</th></tr>
        <tr><th bgcolor='#9999ee'>#</th><th bgcolor='#9999ee'>Function</th><th bgcolor='#9999ee'>Location</th></tr>
        <?php
        foreach ($bt as $key => $call) {
            print "<tr><td bgcolor='#ddddff' align='center'>$key</td><td bgcolor='#ddddff'>";
            print  $call['class'].$call['type'].$call['function']."(";
            $args = array();
            foreach($call['args'] as $arg) {
                print "<br/>";
                if (is_string($arg)) {
                    print "<font color='green'>'$arg'</font>";
                } else if ($full) {
                    if (function_exists("xdebug_memory_usage")) {
                        var_dump($arg);
                    } else {
                        print "<pre>";
                        var_dump($arg);
                        print "</pre>";
                    }
                } else if (is_object($arg)) {
                    print   $arg . " " . get_class($arg);
                } else {
                    print $arg;
                }
               
            }
            
            
            print ")</td><td bgcolor='#ddddff'>".$call['file']."<b>:</b>".$call['line']."</td></tr>";
            
        }  
        print "</table></font><br/>";
    }
    
    static function log_memory_usage() {
        $rep =  40 - strlen($_SERVER['REQUEST_URI']);
        if ($rep < 0)  { $rep = 1;}
        if (function_exists("xdebug_memory_usage")) {
            
            error_log($_SERVER['REQUEST_URI'] .str_repeat(" ", $rep ). " use: " . round(xdebug_memory_usage()/1024/1024,2) ." MB" .
            " peak: " . round(xdebug_peak_memory_usage()/1024/1024,2) ." MB");
        } else if(function_exists('memory_get_usage')){
            if(function_exists('memory_get_usage')) {
                error_log($_SERVER['REQUEST_URI'] .str_repeat(" ", $rep  ). " use: " . round(memory_get_usage()/1024/1024,2) ." MB" );
            }
        }
    }
    
    static function dump_incFiles($sort = false) {
        if ($sort) {
            asort(self::$incFiles);
        }
        var_dump(self::$incFiles);   
    }
    
    static function dump_errorlog($var,$start=0) {
        
        $bt = debug_backtrace();
        $str = "BX_DUMP: " . str_replace(BX_PROJECT_DIR,"",$bt[$start]['file']) ."[" . $bt[$start]['line'] ."]:";
        $str .= $bt[$start+1]['class'].$bt[$start+1]['type'].$bt[$start+1]['function'];
        $str .= " var: " . var_export($var,true);
        error_log($str);
    }   
    
    static function webdump($var,$start=0) {
        $bt = debug_backtrace();
        print "<pre>";
        print  str_replace(BX_PROJECT_DIR,"",$bt[$start]['file']) ."[" . $bt[$start]['line'] ."]:\n";
        print $bt[$start+1]['class'].$bt[$start+1]['type'].$bt[$start+1]['function'] .": \n";
        print var_dump($var);
        print "</pre>";
    }
}


