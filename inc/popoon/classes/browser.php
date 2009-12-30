<?php
    

class popoon_classes_browser {
        
    static private $BrowserName = "Unknown";
    static private $BrowserSubName = "None";
    static private $Version = "Unknown";
    static private $Platform = "Unknown";
    static private $UserAgent = "Not reported";
    static private $isMobile = null;
    
    static private $initialized = false;
    static private $parsed = false;
    
    private function __construct() {}
    
    static function init() {
        if (!self::$initialized) {
            if (isset( $_SERVER['HTTP_USER_AGENT'])) {
                self::$UserAgent = $_SERVER['HTTP_USER_AGENT'];
            }
            self::$initialized = true;
        }
    }
    
    
    static function isMozilla() {
        return( self::getName() == "mozilla");
    }
    
    static function isMozillaAndHasMidas() {
        if (self::getName() == "mozilla") {
            if (stripos(self::$UserAgent,"camino/0.8.")) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
    
    static function hasContentEditable() {
        return (self::isMozillaAndHasMidas() || self::isMSIEWin());
           
    }
    
    
    
    
    static function hasBadCss() {
           self::init();
           $name = self::getName();
           $version = self::getVersion();
           if ($name == "opera" && $version < 8) {
               return true;
           }
           if ($name == "mozilla" && $version < 5) {
               return true;
           }
           return false;
           
    }
    static function isPalm() {
        return (self::getPlatform()=="palm");
    }
    
    static function isMobile() {
        // creating the WURFL object
        if (self::$isMobile === null) {
            require_once(BX_INCLUDE_DIR.'/wurfl_config.php');
            require_once(BX_INCLUDE_DIR.'/wurfl_class.php');
            
            $myDevice = new wurfl_class($wurfl, $wurfl_agents);
            $myDevice->GetDeviceCapabilitiesFromAgent($_SERVER["HTTP_USER_AGENT"]);
            if($myDevice->capabilities['xhtml_ui']['html_wi_w3_xhtmlbasic']){
                self::$isMobile = true;
            }
            else{
                self::$isMobile = false;
            }
        }
        return self::$isMobile;
    }
    
    static function supportedByFCK() {
        
        return (self::isMozilla() || self::isMSIEWin() || self::isSafari3() || self::isOpera95());
    }

    static function isMSIEWin() {
        return( self::getName() == "msie" && self::getPlatform()=="windows");
    }
    
    static function isSafari() {
        return( self::getName() == "safari" );
    }
    
    static function isSafari3() {
        return( self::getName() == "safari" && self::getVersion() >= 3);
    }
    
    static function isOpera8() {
        return( self::getName() == "opera"  && self::getVersion() >= 8);
    }
     static function isOpera95() {
        return( self::getName() == "opera"  && self::getVersion() >= 9.5);
    }
    
    
    static function isKonqueror34() {
        return( self::getName() == "konqueror"  && self::getVersion() >= 3.4);
    }
    
    static function getName() {
        self::parse();
        return self::$BrowserName;
    }
    static function getSubName() {
        self::parse();
        return self::$BrowserSubName;
    }
    
    static function getVersion() {
        self::parse();
        return self::$Version;
    }
    
    static function getPlatform() {
        self::parse();
        return self::$Platform;
    }
    
    static function getAgent() {
        self::init();
        return self::$UserAgent;
    }
    
    static function parse(){
        
        if (!self::$parsed) {
            self::init();
            $agent = self::$UserAgent;
            // initialize properties
            $bd['platform'] = "Unknown";
            $bd['browser'] = "Unknown";
            $bd['version'] = "Unknown";

            // find operating system
            $OSs = array(
                'win' => 'windows',
                'mac' => 'macintosh',
                'linux' => 'linux',
                'os/2' => 'os/2',
                'beos' => 'beos',
                'palm' => 'palm',
            );
            foreach ($OSs as $OS => $OSname) {
                if (stripos($agent, $OS) !== false) {
                    $bd['platform'] = $OSname;
                    break;
                }
            }

            // test for Opera
            if ($val = stristr($agent, "opera")) {
                if (stripos($val, '/') !== false) {
                    $val = explode("/",$val);
                    $bd['browser'] = $val[0];
                    $val = explode(" ",$val[1]);
                    $bd['version'] = $val[0];
                }else{
                    $val = explode(" ",stristr($val,"opera"));
                    $bd['browser'] = $val[0];
                    $bd['version'] = $val[1];
                }

                // test for WebTV
            }elseif(stripos($agent, 'msie') !== false){
                $val = explode(" ",stristr($agent,"msie"));
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'galeon') !== false){
                $val = explode(" ",stristr($agent,"galeon"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['version'] = $val[1];
                $bd['subbrowser']=$val[0];

                // test for Konqueror
            }elseif(stripos($agent, 'konqueror') !== false){
                $val = explode(" ",stristr($agent,"Konqueror"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'firebird') !== false){
                $bd['browser']="Mozilla";
                $bd['subbrowser']="Firefox";
                $val = stristr($agent, "Firebird");
                $val = explode("/",$val);
                $bd['version'] = $val[1];

                // test for Firefox
            }elseif(stripos($agent, 'firefox') !== false){
                $bd['browser']="Mozilla";
                $bd['subbrowser'] = "Firefox";
                $val = stristr($agent, "Firefox");

                $val = explode("/",$val);
                $bd['version'] = $val[1];
            }elseif(stripos($agent, 'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i', $agent) && stripos($agent, 'netscape' === false)){
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Mozilla";
                $val = explode(" ",stristr($agent,"rv:"));
                preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$agent, $val);
                $bd['version'] = str_replace("rv:","",$val[0]);

            }elseif(stripos($agent, 'safari') !== false){
                $bd['browser'] = "Safari";
                $val = substr($agent,strpos($agent,"Safari/") + 7);
                $bd['version'] = $val;

                // remaining two tests are for Netscape
            }elseif(stripos($agent, 'netscape') !== false){
                $val = explode(" ",stristr($agent,"netscape"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'mozilla') !== false && !preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i', $agent)){
                $val = explode(" ",stristr($agent,"mozilla"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Netscape";
                if (isset($val[1])) {
                    $bd['version'] = $val[1];
                }
            }

            // clean up extraneous garbage that may be in the name
            $bd['browser'] = preg_replace('/[^a-zA-Z]/', '', $bd['browser']);
            // clean up extraneous garbage that may be in the version
            $bd['version'] = preg_replace('/[^0-9.a-zA-Z]/', '', $bd['version']);

            // finally assign our properties
            self::$BrowserName = strtolower($bd['browser']);
            if (isset($bd['subbrowser'])) {
                self::$BrowserSubName = strtolower($bd['subbrowser']);
            }
            self::$Version = $bd['version'];
            self::$Platform = $bd['platform'];
            self::$parsed = true;
        }
    }
}
