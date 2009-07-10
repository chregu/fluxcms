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
    protected $auth_gidcol = 'user_gid';
    protected $auth_gupicol = 'user_gupi';
    protected $auth_emailcol = 'user_email';
    protected $auth_dbfields = 'user_adminlang, user_gid, user_email';
    protected $auth_sessname = '_authsession';

    protected $advancedsecurity = false;
    protected $auth_idcol = 'id';
    /**
     * auth password crypt method
     *
     * @access  protected
     * @var     string
     */
    protected $auth_crypttype = 'md5';


    protected function __construct($options = null) {
        if (is_array($options)) {


            $this->initOptions($options);
        }
    }

    protected function initOptions($options) {

        if (!empty($options['auth_overwriteDbfields']) && $options['auth_overwriteDbfields'] == 'true') {
            //$options['auth_dbfields'] =
        } else if (!empty($options['auth_dbfields']) && trim($options['auth_dbfields']) != '') {
            $options['auth_dbfields'] .= "," . $this->auth_dbfields;

        } else {
            $options['auth_dbfields'] = $this->auth_dbfields;
        }

        if (!empty($options['adv_useragentcheck']) && $options['adv_useragentcheck'] == 'true') {
            $this->advancedsecurity = array();
            $this->advancedsecurity[AUTH_ADV_USERAGENT] = true;
        }

        if (!empty($options['adv_ipcheck']) && $options['adv_ipcheck'] == 'true') {
            if (!is_array($this->advancedsecurity)) {
                $this->advancedsecurity = array();
            }
            $this->advancedsecurity[AUTH_ADV_IPCHECK] = true;
        }

        $options['advancedsecurity'] = $this->advancedsecurity;
        foreach ($options as $name => $value) {
            if (isset($this->$name)) {
                $this->$name = $value;
            }
        }
        return $options;

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
        $prts = parse_url(BX_WEBROOT);
        $path = $prts['path'];

        if (empty($_SESSION['_authsession']['registered']) && empty($_POST) && !empty($_COOKIE['fluxcms_login']) ) {
                list($_POST['username'],$_POST['password']) = explode(":", $_COOKIE['fluxcms_login']);
        } elseif (!empty($_POST) && !empty($_POST['remember']) && !empty($_POST['username']) && !empty($_POST['password'])) {
                $hash = $_POST['username'].':'.md5($_POST['username'].md5($_POST['password']));
                if (! (isset($_COOKIE['fluxcms_login']) && $_COOKIE['fluxcms_login'] == $hash)) {
                    setcookie('fluxcms_login',$hash, time() + 3600*24*365, $path ,null,null,true);
                    $_COOKIE['fluxcms_login'] = $hash;
                }
        }
        $this->authObj->assignData();
        $u = $this->specialEncode($this->authObj->username);
        $p = $this->specialEncode($this->authObj->password);
        ini_set('session.cookie_path', $path);
        @session_start();
        if (!$this->authObj->checkAuth() && $this->authObj->showLogin) {
            $this->authObj->login();
            session_regenerate_id(true);
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

    public function getUserGid() {
        return @$_SESSION[$this->auth_sessname]['data'][$this->auth_gidcol];
    }

    public function getUserId() {
        @session_start();
        return $this->authObj->getUserId();
    }

    protected function MDB2Constructor($options,$pearcontainer = 'MDB2',$additionalOpts = array()) {
          $opts = array(
            'dsn'           => $this->dsn,
            'usernamecol'   => $this->auth_usernamecol,
            'passwordcol'   => $this->auth_passwordcol,
            'gupicol'       => $this->auth_gupicol,
            'emailcol'      => $this->auth_emailcol,
            'idcol'         => $this->auth_idcol,
            'db_fields'     => $this->auth_dbfields,
            'cryptType'     => $this->auth_crypttype,
            );

        if (empty($options['auth_prependTablePrefix']) || $options['auth_prependTablePrefix'] == 'true') {
          $opts['table'] =  $GLOBALS['POOL']->config->getTablePrefix().$this->auth_table;
        } else {
            $opts['table'] = $this->auth_table;
        }
        foreach ($additionalOpts as $key) {
            if (isset($options[$key])) {
                $opts[$key] = $options[$key];
            }
        }

        // if someone tries to "login" via http_auth, let them do that :)
        if ((!empty($_GET['httpauth'])) | (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $GLOBALS['POOL']->config->allowHTTPAuthentication == "true") ) {
            $opts['mode'] = '0644';
            $this->authObj = new Auth_HTTP($pearcontainer, $opts);
            $this->authObj->realm = 'Flux CMS HTTP Auth Login';
        } else {
            $this->authObj = new Auth($pearcontainer, $opts, "bxLoginFunction");
        }
    }

    /**
     * wrapper function for Auth modules setAuth() method
     *
     * @param string $username
     * @return void
     */
    public function setAuth($username) {
        // make sure this really is an instance of the Auth module
        if($this->authObj instanceof Auth) {
            $this->authObj->setAuth($username);
        }
    }

}


/**
 * BX Function for Loginscreen
 * does nothing since loginscreen is
 * handled via sitemap
 *
 */
function bxLoginFunction() {
    return true;
}


?>
