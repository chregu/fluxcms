<?php

abstract class bx_permm_auth_common {
    
    /**
     * the auth module object
     * 
     * @access   protected
     * @var      object
     */
    protected $authObj = null;
    
    /**
     * db dsn
     *
     * @access   protected
     * @var      array
     */
    protected $dsn = array();
    
    /**
     * auth table
     *
     * @access  protected
     * @var     string
     */
    protected $auth_table = 'users';
    
    /**
     * auth table username column
     *
     * @access  protected
     * @var     string
     */
    protected $auth_usernamecol = 'login';
    
    /**
     * auth table password tolumn
     *
     * @access  protected
     * @var     string
     */
    protected $auth_passwordcol = 'password';
    
    protected $specialencoding = '';
    
    protected $auth_gupicol = 'user_gupi';
    protected $auth_emailcol = 'user_email';
    protected $auth_dbfields = 'user_adminlang, user_gid, user_email';
    
    protected $auth_idcol = 'id';
    /**
     * auth password crypt method 
     *
     * @access  protected
     * @var     string
     */
    protected $auth_crypttype = 'md5';
    
    
    protected function __construct($options) {
        if (is_array($options)) {
            
            if (isset($options['auth_dbfields'])) {
                if (trim($options['auth_dbfields']) == '' ) {
                    unset($options['auth_dbfields']); 
                } else {
                    //$options['auth_dbfields'] .= "," .$this->auth_dbfields;
                }
            } 
            foreach ($options as $name => $value) {
                if (isset($this->$name)) {
                    $this->$name = $value;
                }
            }
            
        }
    }
    
    
    /**
     * Wrapper function for the auth object - 
     * interface to the permm object,
     * to start authentication process
     *
     * @access  public
     * @return  void
     */
    public function start() {
        if (empty($_SESSION['_authsession']['registered']) && empty($_POST) && !empty($_COOKIE['fluxcms_login']) ) {
                list($_POST['username'],$_POST['password']) = explode(":", $_COOKIE['fluxcms_login']);
        } elseif (!empty($_POST) && !empty($_POST['remember']) && !empty($_POST['username']) && !empty($_POST['password'])) {
                $hash = $_POST['username'].':'.md5($_POST['username'].md5($_POST['password']));
                if (! (isset($_COOKIE['fluxcms_login']) && $_COOKIE['fluxcms_login'] == $hash)) {
                    setcookie('fluxcms_login',$hash, time() + 3600*24*365,"/");
                    $_COOKIE['fluxcms_login'] = $hash;
                }
        }
        
        $this->authObj->assignData();
        $u = $this->specialEncode($this->authObj->username);
        $p = $this->specialEncode($this->authObj->password);
        @session_start();
        if (!$this->authObj->checkAuth() && $this->authObj->showLogin) {
            $this->authObj->login();
        }

    }
    
    
    protected function specialEncode(&$prm) {
        if (isset($this->specialencoding) && !empty($this->specialencoding)) {
            $m = "specialEncode".ucfirst($this->specialencoding);
            if (method_exists($this, $m)) {
                return $this->$m($prm);
            }
        }
    }
    
    protected function specialEncodeEntities(&$prm) {
        $prm = bx_helpers_string::utf2entities($prm);
        return $prm;
    }
    
    /**
     * Wrapper function for the auth object - 
     * interface to the permm object 
     * to check authenticated user
     *
     * @access   public
     * @return   boolean
     */
    public function getAuth() {
        return $this->authObj->getAuth();
    
    }
    

    

    /**
    * Wrapper function for auth object's logout() method
    *
    * @return   void
    * @access   public
    */
    public function logout() {
        if (method_exists($this->authObj, "logout")) {
            $this->authObj->logout();
        }
    
        return NULL;
    }
    
    /**
     * Wrapper function for the auth object -
     * interface to the permm object
     * to get information about current 
     * authentication status
     *
     */
    public function getStatus() {
        return $this->authObj->getStatus();
    }
    
    public function getUsername() {
        return $this->authObj->getUsername();
    }
    
    public function getUserId() {
        @session_start();
        return $this->authObj->getUserId();
    }


}



?>
