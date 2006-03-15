<?php

define('BXCMS_VERSION', "1.4-dev");
define('BXCMS_BUILD','200603150500');
define('BXCMS_REVISION',preg_replace('/\$Rev: ([0-9]+) \$/','r$1','$Rev$'));
    

class bx_init {
    
    static $tmpdir = "./tmp/";
    static $bxdir = "./inc/bx/";
    static $lastdbversion = 6594;
    static $configCachedFile = '' ;
    
    
    static function start($configfile, $root = null) {
        //start install, if no $configfile
        if ($root ) {
            define ('BX_INIT_ROOT',realpath($root).'/');
            self::$tmpdir = BX_INIT_ROOT.'tmp';
            self::$bxdir = BX_INIT_ROOT.'inc/bx';
            $configfile = BX_INIT_ROOT.$configfile;
            
            
        } else {
            define ('BX_INIT_ROOT','./');
        }
        $configCachedFile  = self::$tmpdir . '/config.inc.php';
        self::$configCachedFile = $configCachedFile;
        $cmtime = (int) @filemtime($configfile);
        if ( $cmtime >= (int) @filemtime($configCachedFile) || $cmtime == 0) {
            if (!file_exists($configfile)) {
                header("Location: ./install/");
                die();
            }
            
            include_once(self::$bxdir.'/config/generate.php');
            bx_config_generate::generateCachedConfigFile($configfile, self::$bxdir, self::$tmpdir );   
        }
        

        include_once($configCachedFile);
        
        require_once(BX_LIBS_DIR.'autoload.php');

        $GLOBALS['POOL'] = popoon_pool::getInstance("bx_config");
        $GLOBALS['POOL']->debug = false;
        
        //set path variable
        if (!isset($_GET['path']) || (!$_GET['path'])) {
            $_GET['path'] = "index.html";
        }
        $bx_config = $GLOBALS['POOL']->config;

        $bx_config->staticFileCache = false;

        ini_set('include_path',BX_INCLUDE_DIR.PATH_SEPARATOR.ini_get('include_path'));
        //for staging, will be improved later
        define('BX_STAGE','edit');                
        bx_errorhandler::getInstance();
        
        include_once($configCachedFile.'.post');
        //autoupdate code  
        if (isset($bx_config->autodbupdate) && $bx_config->autodbupdate == 'true') {
            $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
            $lastVersion = $GLOBALS['POOL']->db->queryOne('select value from '.$tablePrefix.'options where name = "lastdbversion"');
            if (!$lastVersion || $lastVersion < self::$lastdbversion) {
                if (isset($_GET['dbupdate'])) {
                    print "Something went wrong with the dbupdate, please inform the administrator";
                } else {
                    include_once(BX_LIBS_DIR."/tools/dbupdate/update.php");
                    header("Location: ".bx_helpers_uri::getRequestUri());
                    
                    print "\nDB updated. You should be forwarded to the requested site. If not click <a href='".bx_helpers_uri::getRequestUri()."?dbupdate=1'>here</a>\n";
                    
                    print '<meta http-equiv="refresh" content="0; URL='.bx_helpers_uri::getRequestUri().'?dbupdate=1"/>';
                }
                die();
            }
        }
        if ($bx_config['defaultLanguage'] == 'auto') {
            $bx_config['defaultLanguage'] = popoon_helpers_lang::preferredBrowserLanguage($bx_config['outputLanguages']);
        }
        if (!defined('BX_DEFAULT_LANGUAGE')) {
            define('BX_DEFAULT_LANGUAGE',$bx_config['defaultLanguage']);
        }
        
        if (!defined('BX_WEBROOT_W')) {
                define ('BX_WEBROOT_W', substr(BX_WEBROOT,0,-1));
        }
    }
    
    static function initDBOptions($notAllowedDBOptions,$optionsMergeArray) {
        $db = $GLOBALS['POOL']->db;
        $res = @$db->query("select name, isarray, value from ".$GLOBALS['POOL']->config->getTablePrefix()."options where value != '' ");
        
        if ($res && !$db->isError($res)) { 
            while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                //only use them, if they have a value
                if (!in_array($row['name'],$notAllowedDBOptions)) {
                    if ($row['value'] !== NULL && $row['value'] !== "") {
                        if ($row['isarray']) {
                            if (in_array($row['name'],$optionsMergeArray) ) {
                                
                                $GLOBALS['POOL']->config->$row['name'] = array_unique(array_merge($GLOBALS['POOL']->config->$row['name'], explode(";",html_entity_decode($row['value'],ENT_COMPAT,'UTF-8'))));
                            } else {
                                $GLOBALS['POOL']->config->$row['name']  = explode(";",html_entity_decode($row['value'],ENT_COMPAT,'UTF-8'));
                            }
                        } else {
                            $GLOBALS['POOL']->config->$row['name'] = html_entity_decode($row['value'],ENT_COMPAT,'UTF-8');
                        }
                    }
                }
            }
        }
    }
    
    static function touchConfigFile() {
        
        if ( !isset($_GET['deleteConfigCache']) && @unlink(self::$configCachedFile) )  {
            bx_helpers_debug::dump_errorlog("Forced unlink of ".self::$configCachedFile);
            //header("Location: ".bx_helpers_uri::getRequestUri("deleteConfigCache=1"));
            //die();
        }
    }
    
}
