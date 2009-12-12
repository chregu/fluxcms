<?php

class bx_versioning_svn {
    
    private static $instance=null;
    
    private $reporoot = '';
    private $svnroot = '';
    private $coroot = '';
    public $enabled = true;
    
    
    private function __construct($opts=array()) {
        $this->reporoot = str_replace("//","/",BX_OPEN_BASEDIR."/repo");
        $this->svnroot = $this->reporoot."/svn";
        $this->coroot = $this->reporoot."/checkout";
        $this->init();
    
    }
    
    public static function getInstance($opts=array()) {
        if (!bx_versioning_svn::$instance instanceof bx_versioning_svn) {
            bx_versioning_svn::$instance = new bx_versioning_svn($opts);
        }
        
        return bx_versioning_svn::$instance;
    }                            
    
    public function cat($path, $rev){
        $path=preg_replace("#\/{2,}#","/",$path);
        print svn_cat($this->coroot.$path, $rev);
    }
    
    public function delete($srcs,$log = 'Deleted') {
            $log = $_GET['dellog'];
            $fullsrcs = array();
            foreach($srcs as $src) {
                $fullsrcs[] = $this->coroot.$src;
            }
            svn_delete($fullsrcs, true);
            svn_commit($log, $fullsrcs);
    }
    
    public function copy($src, $dest, $log = 0,$rev = 0) {
            $log = $_GET['copylog'];
            $src = bx_helpers_string::removeDoubleSlashes($this->coroot.$src);
            $dest = bx_helpers_string::removeDoubleSlashes($this->coroot.$dest);
            svn_copy($src,$dest,$rev);
            svn_commit($log,array($dest));
    }
    
    public function move($src, $dest, $log = 0,$rev = 0) {
            $log = $_GET['movelog'];
            $src = bx_helpers_string::removeDoubleSlashes($this->coroot.$src);
            $dest = bx_helpers_string::removeDoubleSlashes($this->coroot.$dest);
            svn_move($src,$dest,$rev);
            
            svn_commit($log,array($dest));
    }
    
    public function add($src) {
        $src = bx_helpers_string::removeDoubleSlashes($src);
        if (file_exists($src)) {
            return svn_add($src, true, true);
        }
    } 
    
    public function log($reallink,$revision = -2){
        $reallink = bx_helpers_string::removeDoubleSlashes($this->coroot.'/'.$reallink);
        $logs = svn_log($reallink,$revision);
        return $logs;
    }
    
    public function commit($rpath, $log='') {
        if (!$this->enabled) return false;
        
        $this->setUser();
        $path = BX_OPEN_BASEDIR.$rpath;
        
        
        $wdpath = preg_replace("#\/{2,}#","/",$this->coroot."/".$rpath);
        if (!file_exists(dirname($wdpath))) {
            mkdir(dirname($wdpath),0775,true);
        }
        copy($path, $wdpath);
        $elevel = error_reporting();
        error_reporting($elevel & ~ E_WARNING);
        $status = svn_status($wdpath, false, true, true, true);
        if (!$status || !isset($status[0]['revision'])) {
            $dirs = $this->coroot;
            foreach(explode('/', $rpath) as $dir) {
                if (empty($dir)) { continue; }
                $dirs.= '/'.$dir;
                $this->add($dirs);
                svn_commit($log, array($dirs));
            }
        } else {
            
            svn_commit($log, array($wdpath));
        }
        error_reporting($elevel);
        
        svn_repos_recover($this->svnroot);
    }
    
    
    /**
    * initializes versioning and setup prerequisites
    * @acess    public
    * @return   void|false
    */
    public function init() {
        if (!extension_loaded('svn')) {
            try {
                dl('svn.'.PHP_SHLIB_SUFFIX);
            } catch (Exception $e) {
                $this->enabled = false;
                error_log("Error loading svn extension ".$e->getMessage());
                return false;
            }
        }
        
        $this->setUser();
        if (!is_dir($this->reporoot)) { 
            mkdir($this->reporoot, 0775, true); 
        }
        
        if (!is_dir($this->svnroot)) {
            mkdir($this->svnroot, 0775,true);
            try {
                svn_repos_create($this->svnroot, null, array(SVN_FS_CONFIG_FS_TYPE => SVN_FS_TYPE_FSFS));
            } catch (Exception $e) {
                return false;
            }
        }
        
        if (!is_dir($this->coroot)) {
            mkdir($this->coroot, 0775);
            try {
                svn_checkout('file://'.$this->svnroot, $this->coroot);
            } catch (Exception $e) {
                return false;
            }
        }
        
        return null;
    }
    
    private function setUser() {
        $user = bx_permm::getInstance()->getUsername();
        
        if ($user) {
            svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, $user);
        }
        
    }
    


    
}


?>
