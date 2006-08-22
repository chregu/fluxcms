<?php

class bx_config extends popoon_classes_config {
    
    public $theme;
    public $timezoneSeconds = null;
    public $timezoneString = null;
    public $advancedRedirect = null;
    private $language;
    
    private $editorsByPlugin = array();
    // 63 is MDB2 default
    // 31 = 63 &~ MDB2_PORTABILITY_EMPTY_TO_NULL
    public $portabilityoptions = 31;
    public $allowPHPUpload = false;
    public $adminLanguage = NULL;
    public static $instance = NULL;    
    static protected $optionIsArray = array('outputLanguages', 'image_allowed_sizes', 'adminLanguages','blogWeblogsPing');
    
    public static function getInstance () {
        if (!bx_config::$instance) {
            bx_config::$instance = new bx_config();
        } 
        return bx_config::$instance;
    }
    
      
    protected function __construct() {
        parent::__construct();
    }
    
    public function setOutputLanguage($lang) {
        $this->language = $lang;
    }

    public function getOutputLanguage() {
        if (!isset($this->language)) {
            $this->language = BX_DEFAULT_LANGUAGE;
        }
        return $this->language;
    }
    
    public function getOutputLocale() {
        return $this->getLocaleByLanguage($this->getOutputLanguage());
    }
    
    protected function getLocaleByLanguage($lang = BX_DEFAULT_LANGUAGE) {
        switch($lang) {
            case 'en':
                return 'en_UK';
            case 'de':
                return 'de_CH';
            default:
                return $lang;
        }
    }
    
    public function getDefaultOutputLanguage() {
        return BX_DEFAULT_LANGUAGE;
    }

    public function getOutputLanguages() {
        return $this->outputLanguages; 
    }
    
    public function getAdminLanguage() {
        if(isset($this->adminLanguage)) {
            return $this->adminLanguage;
        }
            
        // for fckconfig.js, this is called before the session has been started
        if(!isset($_SESSION)) {
            session_start();
        }
        
        if(!isset($this->adminLanguages)) {
            $this->adminLanguages = array('en');
        }
            
        if(isset($_SESSION['_authsession']['data']['user_adminlang']) && in_array($_SESSION['_authsession']['data']['user_adminlang'], $this->adminLanguages)) {
            $this->adminLanguage = $_SESSION['_authsession']['data']['user_adminlang'];
            
        } else {
            // small hack to prevent the ugly error after logging in without
            // having deleted the tmp/ directory before.
            if(!defined('BX_DEFAULT_ADMIN_LANGUAGE')) {
                define('BX_DEFAULT_ADMIN_LANGUAGE','en');
            }
            
            $this->adminLanguage = popoon_helpers_lang::preferredBrowserLanguage($this->adminLanguages, BX_DEFAULT_ADMIN_LANGUAGE);
            if(isset($_SESSION['_authsession']['data'])) {
                $_SESSION['_authsession']['data']['user_adminlang'] = $this->adminLanguage;
            }
            
        }
        
        return $this->adminLanguage;
        
    }
    
    public function getAdminLanguageFromUser() {
        if (isset($_SESSION['_authsession']['data']['id'])) {
            $db = $GLOBALS['POOL']->db;
            if ($db) {
                $prefx = $this->getTablePrefix();
                $id = $_SESSION['_authsession']['data']['id'];
                $adminLangField = 'user_adminlang';
                $res= $db->query('SELECT '.$adminLangField.' FROM '.$prefx.'users WHERE id='.$id);
                if (!MDB2::isError($res)) {
                    $f = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
                    if (isset($f[$adminLangField])) {
                        return $f[$adminLangField];    
                    }
                }
            }
        }

        return NULL;
    }
     
    public function getAdminLocale() {
        return $this->getLocaleByLanguage($this->getAdminLanguage());    
    }
    
    public function getDefaultAdminLanguage() {
        return BX_DEFAULT_ADMIN_LANGUAGE;
    }
    
    static public function getConfProperty($key,$forceTouch = false) {
        if($key == 'adminLanguage') 
            return $GLOBALS['POOL']->config->getAdminLanguage();
        
        if (isset($GLOBALS['POOL']->config->{$key})) {
            return $GLOBALS['POOL']->config->{$key};
        }
        if ($forceTouch) {
            bx_init::touchConfigfile();
        }
        return "";
    }
    
    public function setConfProperty($key,$value) {
        $GLOBALS['POOL']->config->{$key} = $value;
        $db = $GLOBALS['POOL']->db;
        $dbwrite = $GLOBALS['POOL']->dbwrite;
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $cleankey = $key;
        $key = $db->quote($key);
        if (in_array($cleankey,self::$optionIsArray)) {
            $dbwrite->query("update ".$prefix."options set isarray = 1 where name  = $key");
            $value = preg_replace("#[\r\n]+#",";",trim($value)); 
        }
        
        $value = $db->quote(bx_helpers_string::utf2entities($value));
        if ($db->queryOne("select name from ".$prefix."options where name =  $key")) {
            $dbwrite->query("update ".$prefix."options set value = $value where name  = $key");
        } else {
            $dbwrite->query("replace into ".$prefix."options (name,value) values ($key,$value)");
        }
        
        if ($GLOBALS['POOL']->config->cacheDBOptions) {
            @unlink(BX_TEMP_DIR."config.inc.php");
        }
    }
    
    public function getTablePrefix() {
        if (isset($this->dsn['tableprefix'])) {
            return $this->dsn['tableprefix'];
        } else {
            return "";
        }
    }
    
    public function getEditorsByPlugin($plugin) {
        if(!empty($this->editorsByPlugin[$plugin])) {
            return $this->editorsByPlugin[$plugin];
        } 

        $this->editorsByPlugin[$plugin] = array();

        $configFile = BX_PROJECT_DIR.'conf/editors/'.$plugin.'.xml';
        if(file_exists($configFile)) {
            $configXML = new DomDocument();
            if($configXML->load($configFile)) {
                $xp = new Domxpath($configXML);
                $xp->registerNamespace("bxcms","http://bitflux.org/editorconfig");
                
                foreach($xp->query('/bxcms:bxcms/bxcms:editors/bxcms:editor') as $editorNode) {
                    $this->editorsByPlugin[$plugin][] = $editorNode->getAttribute('name');
                }
            }
        }

        return $this->editorsByPlugin[$plugin];
    }
    
}

?>
