<?php
class bx_helpers_captcha {
    
    public static function isCaptcha($days=null, $postdate=null) {
        if ($days < 0) {
            return false;
        }
        
        if (is_int($postdate)) {
            $unixtimepostdate = $postdate;
        } else {
            $unixtimepostdate = strtotime($postdate);
        }
        $captchastart = $unixtimepostdate + ($days * 24 * 60 * 60);
        $unixtimenow = time();
        if($captchastart <= $unixtimenow) {
            return true;
        } else {
            return false;
        }
    }
    
    
    static function checkCaptcha($captcha, $imgid) {
        $magickey = $GLOBALS['POOL']->config->magicKey;
        preg_match("#.*.html#", $_SERVER['REQUEST_URI'], $matches);
        
        
        if($imgid == md5($captcha.floor(time()/(60*15)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0']) or $imgid == md5($captcha.floor(time()/(60*15-1)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0'])) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function doCaptcha() {
        require_once 'inc/Text/CAPTCHA.php';
        
        // Set CAPTCHA options (font must exist!)
        
        $font_path =  $GLOBALS['POOL']->config->blogCaptchaFontPath;
        $font_file =  $GLOBALS['POOL']->config->blogCaptchaFontFile;
        
        if (substr($font_path,0,1) != "/") {
            $font_path = BX_PROJECT_DIR . $font_path;
        }
        $options = array(
        'font_size' => 16,
        'font_path' => $font_path,
        'font_file' => $font_file
        );
        
        // Generate a new Text_CAPTCHA object, Image driver
        $c = Text_CAPTCHA::factory('Image');
        $retval = $c->init(100, 30, null, $options);
        if (@PEAR::isError($retval)) {
            
            echo 'Error generating CAPTCHA!';
            print $retval->getMessage();
            
            exit;
        }
        
        // Get CAPTCHA secret passphrase
        $passphrase = $c->getPhrase();
        $imgid = self::generateCaptchaId($passphrase);
        // Get CAPTCHA image (as PNG)
        $png = $c->getCAPTCHAAsPNG();
        if (@PEAR::isError($png)) {
            echo 'Error generating CAPTCHA!';
            exit;
        }
        if(!is_dir(BX_PROJECT_DIR.'dynimages/captchas/')) {
            mkdir(BX_PROJECT_DIR.'dynimages/captchas/');
        }
        file_put_contents(BX_PROJECT_DIR.'dynimages/captchas/'. $imgid . '.png', $png);
        return $imgid;
    }
    
    public static function generateCaptchaId($passphrase) {
        $magickey = $GLOBALS['POOL']->config->magicKey;
        preg_match("#.*.html#", $_SERVER['REQUEST_URI'], $matches);
        $imgid = md5($passphrase.floor(time()/(60*15)).$magickey.$_SERVER['REMOTE_ADDR'].$matches[0]);
        return $imgid;
    }
    

}
?>
