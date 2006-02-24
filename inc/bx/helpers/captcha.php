<?php
class bx_helpers_captcha {
    /*static function getCaptchaImg() {
        require_once 'inc/Text/CAPTCHA.php';
        $c = Text_CAPTCHA::factory('Image');
        $passphrase = $c->getPhrase();
        $magickey = 3;
        $img = '/dynimages/' . md5($passphrase.floor(time()/60+30).$magickey) . '.png?' . time();
        return $img;
    }
    */
    
    public static function isCaptcha($days=null, $postdate=null) {
        $unixtimepostdate = strtotime($postdate);
        
        $captchastart = $unixtimepostdate + ($days * 24 * 60 * 60);
        
        $unixtimenow = time();
        if($captchastart <= $unixtimenow) {
            return 1;
        } else {
            return 0;
        }
    }
    
    public static function doCaptcha() {
        require_once 'inc/Text/CAPTCHA.php';
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
        
        file_put_contents(BX_PROJECT_DIR.'dynimages/'. $imgid . '.png', $png);
        return $imgid;
    }
    
    public static function generateCaptchaId($passphrase) {
        $magickey = $GLOBALS['POOL']->config->magicKey;
        $days = $GLOBALS['POOL']->config->blogCaptchaAfterDays;
        preg_match("#.*.html#", $_SERVER['REQUEST_URI'], $matches);
        $imgid = md5($passphrase.floor(time()/(60*15)).$magickey.$_SERVER['REMOTE_ADDR'].$matches['0']);
        return $imgid;
    }
    

}
?>
