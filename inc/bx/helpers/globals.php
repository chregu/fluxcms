<?php

class bx_helpers_globals {
    
    
    static function GET($name,$default = "") {
        
        if (isset($_GET[$name])) {
            
            return popoon_classes_externalinput::basicClean($_GET[$name]);
        } else {
            return $default;
        }
        
    }
    
        static function COOKIE($name,$default = "") {
        if (isset($_COOKIE[$name])) {
            return popoon_classes_externalinput::basicClean($_COOKIE[$name]);
        } else {
            return $default;
        }
        
    }
    
    static function stripMagicQuotes($in) {
        if (!get_magic_quotes_gpc()) {
            return $in;
        }
        if (is_array($in)) {
            foreach($in as $key => $value) {
                $in[$key]= self::stripMagicQuotes($value);
            }
        } else {
            return stripslashes($in);
        }
        return $in;
    }
    
    static function isSessionCookieSet() {
      if (isset($_COOKIE[session_name()])) {
          return "true";
      } else if (isset($_COOKIE["fluxcms_login"])) {
        return "true";   
      } else {
          return "false";
      }
      
    }
}
        
