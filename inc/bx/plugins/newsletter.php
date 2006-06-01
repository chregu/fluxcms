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

	protected function __construct ( $mode ) {
		$this -> mode = $mode ;
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
		if(isset($_GET["unsubscribe"]))
		{   
			if(!$this->removeSubscriber($_GET['unsubscribe'])) {
				$_POST["notfound"] = "true";
			}
		}
		// activate an account in case double-opt-in is enabled
		else if(isset($_GET["activate"]))
		{   
			if($this->activateSubscriber($_GET['activate'])) {
				$xml .= '<status>Your subscription has been activated.</status>';
			}
			else {
				$xml .= '<status>ERROR: The provided activation id is invalid.</status>';
			}
		}
		
		if(isset($_POST["invemail"]))
		{
			$xml .= '<status>ERROR: Please supply a valid email address.</status>';
		}		
		else if(isset($_POST["notfound"]))
		{
			$xml .= '<status>ERROR: Your subscription could not be found!</status>';
		}		
		else if(isset($_GET["unsubscribe"]) or isset($_POST["unsubscribe"]))
		{
			$xml .= '<status>Your subscription has been canceled.</status>';
		}
		else if(isset($_POST["duplicate"]))
		{
			$xml .= '<status>ERROR: Your email address is already in use!</status>';
		}
		else if(isset($_POST["subscribe"]))
		{
			$xml .= '<status>Your subscription was added successfully.</status>';
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
        	if($this->removeSubscriber($data['email']) === false) {
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
    protected function removeSubscriber($email){
    	$userid = $this->getUserId($email);

    	// remove user
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "UPDATE ".$prefix."newsletter_users SET status='3' WHERE id='".$userid."'";
        if($GLOBALS['POOL']->dbwrite->exec($query) !== 1) {
        	// could not deactivate user
        	return false;	
        }
        
        // remove from groups
        $query = "delete from ".$prefix."newsletter_users2groups where fk_user='".$userid."'";
        $GLOBALS['POOL']->dbwrite->exec($query);
        
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
        $sections = array();
        $dom = new bx_domdocs_overview();
        
        $dom->setTitle("Newsletter", "Newsletters");
        $dom->setPath($path);
        $dom->setIcon("gallery");

		// first tab
        $dom->addLink("Create Newsletter",'addresource/newsletter/drafts/?type=xhtml');
        $dom->addLink("Send Newsletter",'edit'.$path.'send/');
        $dom->addLink("Newsletter Archive",'edit'.$path.'manage/');
        $dom->addLink("Generate from Feed",'edit'.$path.'feed/');
        
        // second tab
        $dom->addTab("Management");
        $dom->addLink("Edit Users",'dbforms2/newsletter_users/');
        $dom->addLink("Edit Groups",'dbforms2/newsletter_groups/');
        $dom->addLink("Edit Senders",'dbforms2/newsletter_from/');
        $dom->addLink("Edit Mail Servers",'dbforms2/newsletter_mailservers/');
        $dom->addLink("Edit RSS Feeds",'dbforms2/newsletter_feeds/');
        $dom->addLink("User Management",'edit'.$path.'users/');
        
        return $dom;
    }
    
    /**
     * Returns true if the supplied email address is valid and points to an existing DNS record
     */
    protected function checkEmailAddress($email) {
    
    	if(eregi(".+@.+\..+.", $email)) {
    		// doesn't work on windows
    		//list($userName, $mailDomain) = split("@", $email);
    		//if(checkdnsrr($mailDomain, "MX")) { 
    			return true;
    		//}
    	}
    
    	return false;	
    }
}
?>
