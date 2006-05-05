<?php
/**
 * The newsletter plugin manages subscription and unsubscription
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
	
	/**
	 * Generates the newsletter form
	 */
	public function getContentById ($path , $id) {
		
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
	 * Handles events to add or remove subscribers to the newsletter
	 */
    public function handlePublicPost($path, $id, $data) {

        // check double-opt-in
        
        //bx_helpers_debug::webdump($data);     
        
        // write to db
        if(isset($data['subscribe'])){
        	$this->addSubscriber($data);
        }
        else if(isset($data['unsubscribe'])){
        	$this->removeSubscriber($data);
        }
    }

	public function isRealResource ( $path , $id) {
		return true ;
	}
	
	/**
	 * Adds a new subscriber
	 * 
	 * @param data POST form data
	 */
    protected function addSubscriber($data){
        // add subscriber
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "insert into ".$prefix."newsletter_users (firstname, lastname, email) value('".$data['firstname']."', '".$data['lastname']."', '".$data['email']."')";
        $GLOBALS['POOL']->db->query($query);
        
        $userid = $this->getUserId($data['email']);
        
        // add to selected groups
        foreach($data['groups'] as $grp)
        {
        	$query = "insert into ".$prefix."newsletter_lists (fk_user, fk_group) value('".$userid."', '".$grp."')";
        	$GLOBALS['POOL']->db->query($query);
        }
    }
    
    /**
     * Removes the subscriber from all groups
     * 
     * @param data POST form data
     */
    protected function removeSubscriber($data){
    	$userid = $this->getUserId($data['email']);
    	
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "delete from ".$prefix."newsletter_users where id='".$userid."'";
        $GLOBALS['POOL']->db->query($query);
        $query = "delete from ".$prefix."newsletter_lists where fk_user='".$userid."'";
        $GLOBALS['POOL']->db->query($query);
    }
    
    /**
     * Counts the number of groups
     * 
     * @return number of groups
     */
    protected function getNumberOfGroups()
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select count(*) from ".$prefix."newsletter_groups";
        return $GLOBALS['POOL']->db->queryOne($query);	
	}
	
	/**
	 * Returns an array of newsletter groups
	 * 
	 * @return associated array
	 */
	protected function getGroups()
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_groups";
        $res = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	
        return $res;		
	}
	
	/**
	 * Retrieves the primary key for a user from his unique email address
	 * 
	 * @param email email address as string
	 */
	protected function getUserId($email)
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select id from ".$prefix."newsletter_users where email='".$email."'";
        return $GLOBALS['POOL']->db->queryOne($query);	
	}
	 
	public function getEditorsById($path, $id) {
        return array("newsletter");
       	
    }
    
    public function getMimeTypes() {
        //return array("text/html","text/wiki");
        return array("text/html");
    }
    /*
    public function getResourceTypes() {
        return array("xhtml");
    }*/
    
    
    
    
  	
    
    /**
     * Admin page interface
     */
	public function getOverviewSections($path) {
        $sections = array();
        $dom = new bx_domdocs_overview();
        
        $dom->setTitle("Newsletter");
        $dom->setPath($path);
        $dom->setIcon("gallery");
        
        $dom->addLink("Create Newsletter",'edit'.$path.'create/');
        $dom->addLink("Show Newsletters",'edit'.$path.'show/');
        
        $dom->addTab("Subscribers");
        $dom->addLink("Edit Users",'dbforms2/newsletter_users/');
        $dom->addLink("Edit Groups",'dbforms2/newsletter_groups/');
        $dom->addLink("Edit Lists",'dbforms2/newsletter_lists/');
        
        return $dom;
    }
    
	public function adminResourceExists($path, $id, $ext=null, $sample = false) {
		if($ext == 'xhtml') {
			return false;
		} 
        return true;
    }
    
   	
}
?>
