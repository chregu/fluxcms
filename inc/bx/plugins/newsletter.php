<?php
/**
* User interface for the newsletter plugin
*/
class bx_plugins_newsletter extends bx_plugin implements bxIplugin {
    
    static public $instance = array ();
    
    public static function getInstance ( $mode ) {
        if (! isset ( self::$instance [ $mode ])) {
            self::$instance[$mode] = new bx_plugins_newsletter ( $mode );
        }
        return self::$instance [ $mode ];
    }
    
    public function __construct ( $mode  = "output") {
        $this -> mode = $mode ;
    }
    
    public function getPermissionList() {
        return array(    "newsletter-back-feed",
        "newsletter-back-send",
        "newsletter-back-archive",
        "newsletter-back-manage",
        "admin_dbforms2-back-newsletter_feeds",
        "admin_dbforms2-back-newsletter_from",
        "admin_dbforms2-back-newsletter_groups",
        "admin_dbforms2-back-newsletter_users",
        "admin_dbforms2-back-newsletter_mailservers");    
    }
    
    public function getEditorsById($path, $id) {
        return array("newsletter");
        
    }
    
    public function getMimeTypes() {
        return array("text/html");
    }
    
    public function isRealResource ( $path , $id) {
        return true ;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        if($ext == 'xhtml') {
            return false;
        } 
        return true;
    }
    
    /**
    * Newsletter subscription form
    */
    public function getContentById ($path , $id) {
        
        $xml ='<newsletter>';
        
        // enable to unsubscribe directly over the URL by appending ?unsubsribe={email}
        // this is needed in order to add unsubsribe-links to newsletter mails
        if(isset($_GET["unsubscribe"]) && !empty($_GET['really']))
        {   
            if(!$this->removeSubscriber($_GET['unsubscribe'], ($_GET['groups']))) {
                $_POST["notfound"] = "true";
            }
        }
        // activate an account in case double-opt-in is enabled
        else if(isset($_GET["activate"]))
        {   
            if($this->activateSubscriber($_GET['activate'])) {
                $xml .= '<status>SUB_ACT_OK</status>';
            }
            else {
                $xml .= '<status>SUB_ACT_ID_NOTFOUND</status>';
            }
        }
        
        if(isset($_POST["invemail"]))
        {
            $xml .= '<status>SUB_EMAIL_INVAL</status>';
        }        
        else if(isset($_POST["notfound"]))
        {
            $xml .= '<status>SUB_NOT_FOUND</status>';
        }
        else if((isset($_GET["unsubscribe"]) && !empty($_GET['really'])) or isset($_POST["unsubscribe"]))
        {
            $xml .= '<status>SUB_UNSUB_SUCCESS</status>';
            $xml .= '<extended>SUB_CANCELED</extended>';
        }
        
        else if(isset($_GET["unsubscribe"]) or isset($_POST["unsubscribe"]))
        {
            $xml .= '<status>SUB_UNSUB_CONFIRM</status>';
            $xml .= '<extended link="./?unsubscribe='.urlencode($_GET['unsubscribe']).'&really=1&groups='.urlencode($_GET['groups']).'">SUB_UNSUB_CONFIRM_LINK</extended>';
        }
        
        else if(isset($_POST["duplicate"]))
        {
            $xml .= '<status>SUB_EMAIL_INUSE</status>';
        }
        else if(isset($_POST["subscribe"]))
        {
            $xml .= '<status>SUB_THANKS</status>';
            $xml .= '<extended>SUB_OK</extended>';
        }
        
        // pass through the list of public groups to the static.xsl    
        foreach ($this->getGroups() as $row)
        {
            $xml .= '<group id="'.$row['id'].'">'.$row['name'].'</group>';
        }
        $xml .='</newsletter>';
        
        $dom = new DomDocument();
        
        if (!@$dom->loadXML($xml)) {
            //if it didn't work loading, try with replacing ampersand
            //FIXME: DIRTY HACK, works only in special cases..
            $xml = str_replace("&amp;","§amp;",$xml);
            $xml = preg_replace("#\&([^\#])#", "&#38;$1", $xml);
            $xml = str_replace("§amp;","&amp;",$xml);
            $dom->loadXML($xml);
        }
        return $dom;
    }
    
    /**
    * add and remove subscription events
    */
    public function handlePublicPost($path, $id, $data) {
        
        // write to db
        if(isset($data['subscribe'])){
            // validate email address
            if($this->checkEmailAddress($data['field_email']) == false) {
                $_POST["invemail"] = "true";
                return;    
            }
            
            if($this->addSubscriber($data, $path) === false) {
                $_POST["duplicate"] = "true";
            }
        }
        else if(isset($data['unsubscribe'])){
            
            
            if($this->removeSubscriber($data['email'], $data['groups']) === false) {
                $_POST["notfound"] = "true";
            }
        }
    }
    
    /**
    * Add a new subscriber
    */
    protected function addSubscriber($data, $path) {
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        // create a query with all input fields starting with 'field_'
        $queryFields = "";
        $queryValues = "";
        foreach($data as $key => $value)
        {
            if(strncmp($key, "field_", 6) == 0) {
                $queryFields .= substr($key, 6) . ",";
                $queryValues .= $GLOBALS['POOL']->dbwrite->quote($value."") . ",";
                $data[substr($key, 6)] = $value;
            }
        }
        
        // create a random activation id
        $activation = $activation = mt_rand(10000000,99999999);
        $data['activation'] = $activation;
        $status = 1;
        
        // check if the user wants to join a double-opt-in group
        $query = "select id from ".$prefix."newsletter_groups WHERE optin=1 AND public=1";
        $optinGroups = $GLOBALS['POOL']->db->queryCol($query);    
        
        $doubleopt = false;
        if(count(array_intersect($optinGroups, $data['groups'])) > 0) {
            $doubleopt = true;
            $status = 2; // needs activation
        }
        
        // delete old entries with the same email address
        $email = $GLOBALS['POOL']->dbwrite->quote($data['field_email']);
        $query = "delete from ".$prefix."newsletter_users where email=".$email." AND status=3";
        $GLOBALS['POOL']->dbwrite->exec($query);
        
        // add to database
        $query = "insert into ".$prefix."newsletter_users (".$queryFields."activation,status,created) value(".$queryValues."'".$activation."','".$status."',NOW())";
        if($GLOBALS['POOL']->dbwrite->exec($query) !== 1) {
            // could not insert user
            return false;    
        }
        
        $userid = $this->getUserId($data['field_email']);
        
        // add to selected groups
        foreach($data['groups'] as $grp)
        {
            $query = "insert into ".$prefix."newsletter_users2groups (fk_user, fk_group) value('".$userid."', '".$grp."')";
            $GLOBALS['POOL']->dbwrite->exec($query);
        }
        
        if($doubleopt == true) {
            // send user a mail with his activation id
            $newsmailer = bx_editors_newsmailer_newsmailer::newsMailerFactory($this->getParameter($path,"sendclass"));
            $newsmailer->sendActivationMail($data, $this->getParameter($path,"activation-server"), 
            $this->getParameter($path,"activation-from"), $this->getParameter($path,"activation-subject"),
            $this->getParameter($path,"activation-text"), $this->getParameter($path,"activation-html"));
        }
    }
    
    /**
    * Remove a subscriber from all lists
    */
    protected function removeSubscriber($email,$groups = ""){
        
        if (!$groups) {
            return false;
        }
        $userid = $this->getUserId($email);
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
        
        //delete groups
        $query = "delete from ".$prefix."newsletter_users2groups where fk_user='".$userid."' and fk_group in ($groups);";
        $db->exec($query);
        // check if there are more groups
        
        $res = $db->query("select id from ".$prefix."newsletter_users2groups where fk_user = ".$userid);
        if ($res->numRows() == 0) {
        
            // set status to deactivated
        
            $query = "UPDATE ".$prefix."newsletter_users SET status='3' WHERE id='".$userid."'";
            if($db->exec($query) !== 1) {
                // could not deactivate user
                return false;    
            }
        
        }
        return true;
    }
    
    /**
    * Activate the subscriber with the given activation-id
    */
    protected function activateSubscriber($id)
    {
        if($id < 10000000 or $id > 99999999)
        return false;
        
        $id = $GLOBALS['POOL']->dbwrite->quote($id);
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "UPDATE ".$prefix."newsletter_users SET status='1' WHERE activation = ".$id;
        if($GLOBALS['POOL']->dbwrite->exec($query) !== 1) {
            // could not find user
            return false;    
        }
        return true;
    }
    
    /**
    * Returns an associated array of newsletter groups
    */
    protected function getGroups()
    {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_groups WHERE public=1";
        $res = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);    
        return $res;        
    }
    
    /**
    * Retrieves the primary key for a user from his unique email address
    */
    protected function getUserId($email)
    {
        $email = $GLOBALS['POOL']->dbwrite->quote($email);
        
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select id from ".$prefix."newsletter_users where email=".$email;
        return $GLOBALS['POOL']->db->queryOne($query);    
    }
    
    /**
    * Admin view collection interface
    */
    public function getOverviewSections($path) {
        $perm = bx_permm::getInstance();
        
        $sections = array();
        $dom = new bx_domdocs_overview();
        
        $dom->setTitle("Newsletter", "Newsletters");
        $dom->setPath($path);
        $dom->setType("newsletter");
        $dom->setIcon("gallery");
        
        // first tab
        if ($perm->isAllowed($path.'drafts/',array('xhtml-back-create'))) {
            $dom->addLink("Create Newsletter",'addresource'.$path.'drafts/?type=xhtml');
        }    
        if ($perm->isAllowed($path,array('newsletter-back-send'))) {
            $dom->addLink("Send Newsletter",'edit'.$path.'send/');
        }    
        if ($perm->isAllowed($path,array('newsletter-back-archive'))) {
            $dom->addLink("Newsletter Archive",'edit'.$path.'manage/');
        }    
        if ($perm->isAllowed($path,array('newsletter-back-feed'))) {
            $dom->addLink("Generate from Feed",'edit'.$path.'feed/');
        }    
        
        // second tab
        $dom->addTab("Management");
        if ($perm->isAllowed($path,array('newsletter-back-manage'))) {
            $dom->addLink("User Management",'edit'.$path.'users/');
        }    
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-newsletter_users'))) {
            $dom->addLink("Edit Users",'dbforms2/newsletter_users/');
        }
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-newsletter_groups'))) {
            $dom->addLink("Edit Groups",'dbforms2/newsletter_groups/');
        }
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-newsletter_from'))) {
            $dom->addLink("Edit Senders",'dbforms2/newsletter_from/');
        }
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-newsletter_mailservers'))) {
            $dom->addLink("Edit Mail Servers",'dbforms2/newsletter_mailservers/');
        }
        if ($perm->isAllowed('/dbforms2/',array('admin_dbforms2-back-newsletter_feeds'))) {
            $dom->addLink("Edit RSS Feeds",'dbforms2/newsletter_feeds/');
        }        
        
        return $dom;
    }
    
    /**
    * Returns true if the supplied email address is valid and points to an existing DNS record
    */
    protected function checkEmailAddress($email) {
        
        if(eregi(".+@.+\..+.", $email)) {
            // doesn't work on windows
            list($userName, $mailDomain) = split("@", $email);
            if(checkdnsrr($mailDomain, "MX")) { 
                return true;
            }
        }
        
        return false;    
    }
}
?>
