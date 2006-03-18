<?php
class bx_helpers_captcha {
    
    public static function isCaptcha($days=null, $postdate=null) {
        $unixtimepostdate = strtotime($postdate);
        $captchastart = $unixtimepostdate + ($days * 24 * 60 * 60);
        $unixtimenow = time();
        if($captchastart <= $unixtimenow) {
            return true;
        } else {
            return false;
        }
    }
    
    
    static function checkCaptcha($captcha, $imgid) {
        $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
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
        $font_path = 0;
        $font_file = 0;
        
        // Set CAPTCHA options (font must exist!)
        $options = array(
        'font_size' => 16,
        'font_path' => '/usr/share/fonts/truetype/msttcorefonts/',
        'font_file' => 'Courier_New.ttf'
        );
        
        // Generate a new Text_CAPTCHA object, Image driver
        $c = Text_CAPTCHA::factory('Image');
        $retval = $c->init(100, 30, null, $options);
        if (@PEAR::isError($retval)) {
            echo 'Error generating CAPTCHA!';
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
