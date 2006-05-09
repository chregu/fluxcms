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
		
		// enable to unsubscribe directly over the URL by appending ?unsubsribe={email}
		// this is needed in order to add unsubsribe-links to newsletter mails
		if(isset($_GET["unsubscribe"]))
		{   
			$this->removeSubscriber($_GET['unsubscribe']);
		}
		// activate an account in case double-opt-in is enabled
		else if(isset($_GET["activate"]))
		{   
			$this->activateSubscriber($_GET['activate']);
		}
		
		// pass through the list of public groups to the static.xsl
		$xml ='<newsletter>';
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
        	$this->addSubscriber($data);
        }
        else if(isset($data['unsubscribe'])){
        	$this->removeSubscriber($data['email']);
        }
    }
	
	/**
	 * Add a new subscriber
	 */
    protected function addSubscriber($data){
    	
    	$activated = 1;
    	
    	/* TODO: check for double-opt-in
    	if(double-opt-in)
    	{
    		$activated = mt_rand(10000000,99999999);
    		
    		send email...
    	}
    	*/
    	
        // add to database
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "insert into ".$prefix."newsletter_users (firstname, lastname, email, gender, activated) value('".$data['firstname']."', '".$data['lastname']."', '".$data['email']."', '".$data['gender']."', '".$activated."')";
        $GLOBALS['POOL']->dbwrite->query($query);
        
        $userid = $this->getUserId($data['email']);
        
        // add to selected groups
        foreach($data['groups'] as $grp)
        {
        	$query = "insert into ".$prefix."newsletter_users2groups (fk_user, fk_group) value('".$userid."', '".$grp."')";
        	$GLOBALS['POOL']->dbwrite->query($query);
        }
    }
    
    /**
     * Remove a subscriber from all lists
     */
    protected function removeSubscriber($email){
    	$userid = $this->getUserId($email);
    	
    	// remove user
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$prefix."newsletter_users where id='".$userid."'";
        $GLOBALS['POOL']->dbwrite->query($query);
        
        // remove from groups
        $query = "delete from ".$prefix."newsletter_users2groups where fk_user='".$userid."'";
        $GLOBALS['POOL']->dbwrite->query($query);
    }
    
    /**
     * Activate the subscriber with the given activation-id
     */
    protected function activateSubscriber($id)
    {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "UPDATE ".$prefix."newsletter_users SET activated='1' WHERE activated = '".$id."'";
        $GLOBALS['POOL']->dbwrite->query($query);
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
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select id from ".$prefix."newsletter_users where email='".$email."'";
        return $GLOBALS['POOL']->db->queryOne($query);	
	}
    
    /**
     * Admin view collection interface
     */
	public function getOverviewSections($path) {
        $sections = array();
        $dom = new bx_domdocs_overview();
        
        $dom->setTitle("Newsletter");
        $dom->setPath($path);
        $dom->setIcon("gallery");

		// first tab
        $dom->addLink("Create Newsletter",'addresource/newsletter/?type=xhtml');
        $dom->addLink("Send Newsletter",'edit'.$path.'send/');
        $dom->addLink("Newsletter Archive",'edit'.$path.'manage/');
        
        // second tab
        $dom->addTab("Subscribers");
        $dom->addLink("Edit Users",'dbforms2/newsletter_users/');
        $dom->addLink("Edit Groups",'dbforms2/newsletter_groups/');
        $dom->addLink("Edit Mailing Lists",'dbforms2/newsletter_lists/');
        $dom->addLink("Edit Mail Servers",'dbforms2/newsletter_mailservers/');
        $dom->addLink("User Management",'edit'.$path.'users/');
        
        return $dom;
    }
}
?>
