<?php

class bx_errorhandler {

    static private $instance = null;
    public static $standardLevel = E_ALL;

    //pear classes do shite with strict...
    //sitemap.php also at the moment...
    public $excludePath = null;
    private $reports = array();
    public static function getInstance() {
        if (!bx_errorhandler::$instance) {
            bx_errorhandler::$instance = new bx_errorhandler();
        }
        return bx_errorhandler::$instance;
    }

    private function __construct() {
        $this->excludePath = array("Cache","Date", "Config","Text","Image","MDB2","PEAR","Log","log.php","HTTP".DIRECTORY_SEPARATOR."WebDAV","Auth","sitemap.php","HTTP".DIRECTORY_SEPARATOR."Request","HTTP".DIRECTORY_SEPARATOR."Client", "Net","patForms","patError","XML","Auth".DIRECTORY_SEPARATOR."OpenID","Services".DIRECTORY_SEPARATOR."Yadis");
        self::$standardLevel = error_reporting();
        set_error_handler(array($this,"error"),self::$standardLevel);

    }

    public function error($errno, $errstr, $errfile, $errline, $ctx) {
        if ($errno & error_reporting()) {
            switch ($errno) {
                case E_WARNING:
                    $this->addReport("Warning",$errno,$errstr,$errfile,$errline,$ctx);
                break;
                case E_NOTICE:
                    $this->addReport("Notice",$errno,$errstr,$errfile,$errline,$ctx);
                break;
                case E_STRICT:
                $doReport = true;
                foreach ($this->excludePath as $path) {
                    if (strpos($errfile,$path) !== false) {
                        $doReport = false;
                        break;
                    }
                }
                if ($doReport) {
                    $this->addReport("Strict",$errno,$errstr,$errfile,$errline,$ctx);
                }
                break;
                case 8192: //E_DEPRECATED...
                $doReport = true;
                foreach ($this->excludePath as $path) {
                    if (strpos($errfile,$path) !== false) {
                        $doReport = false;
                        break;
                    }
                }
                if ($doReport) {
                    $this->addReport("Deprecated",$errno,$errstr,$errfile,$errline,$ctx);
                }

                break;
                case E_USER_ERROR:
                echo "<b>USER_ERROR</b> [$errno] $errstr<br />\n";
                echo "Fatal error in line $errline of file $errfile";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                exit(1);
                break;

                case E_USER_WARNING:
                    $this->addReport("User Warning",$errno,$errstr,$errfile,$errline,$ctx);
                break;
                case E_USER_NOTICE:
                    $this->addReport("User Notice",$errno,$errstr,$errfile,$errline,$ctx);
                break;
                default:
                echo "Unkown error type: [$errno] $errstr in $errfile at $errline<br />\n";
                break;
            }
        }
    }
    public function addReport($level, $errno, $errstr, $errfile, $errline) {
        $this->reports[] = array("level" => $level, "no" => $errno, "str"=> $errstr, "file"=>str_replace(BX_PROJECT_DIR,"[BX_PROJECT_DIR]/",$errfile),"line"=>$errline);
         $log=$level;
         $log .= "[".$errno."] ".$errstr. ' in '. $errfile . ' at line ' .$errline;
        error_log($log);
    }
    public function getHtml() {

        if (ini_get('display_errors') ) {
            $html = "<div class='error'><hr/><div class='errorTitle'>BXCMSNG Errors:</div>";
            foreach($this->reports as $report) {
                $html .= "<div class='errorReport'><b>".$report['level']."</b>";
                $html .= "[".$report['no']."] ".$report['str']. ' in '. $report['file'] . ' at line ' .$report['line']. ".</div>\n";
            }
            return $html ."</div>";
        } else {
            return "";
        }
    }

    public function hasErrors() {
        return count($this->reports) > 0;
    }

}

?>
