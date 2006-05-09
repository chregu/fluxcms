<?php

/**
 * Admin view of the newsletter collection
 */
class bx_editors_newsletter extends bx_editor implements bxIeditor {    
    
    protected $db_options;
    
    /**
     * Configure Mail_Queue database options
     */
    public function __construct()
    {
		$this->db_options['type']       = 'db';
		$this->db_options['dsn']        = 'mysql://fluxcms:fluxcms@localhost/fluxcms';
		$this->db_options['mail_table'] = 'fluxcms_mail_queue';	
    }
    
    public function getDisplayName() {
        return "Newsletter";
    }

	public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'newsletter');
    }
    
    /**
     * Gathers a list of subscriptors for the newsletter the user wishes to send. 
     */
    public function handlePOST($path, $id, $data) {
 		
 		$parts = bx_collections::getCollectionUriAndFileParts($id);

     	// Send event
     	if($parts['name'] == "send/.")
     	{
     		// Save all the information we received about the newsletter in the database for archiving purposes
     		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
     		$query = 	"INSERT INTO ".$prefix."newsletter_drafts (`from`,`subject`,`htmlfile`, `textfile`)
						VALUES (
						'".$data['from']."', '".$data['subject']."', '".$data['htmlfile']."', '".$data['textfile']."');";
			$GLOBALS['POOL']->dbwrite->query($query);
			
			$draft = $GLOBALS['POOL']->dbwrite->queryOne("SELECT ID FROM ".$prefix."newsletter_drafts ORDER BY ID DESC LIMIT 1;");
			
			foreach($data['groups'] as $grp)
			{
				$query = 	"INSERT INTO ".$prefix."newsletter_drafts2groups (`fk_draft`,`fk_group`)
							VALUES (
							'".$draft."', '".$grp."');";
				$GLOBALS['POOL']->dbwrite->query($query);
			}
			
			// Get a unique list of subscriptors
			$query = "SELECT DISTINCT firstname, lastname, email, gender FROM ".$prefix."newsletter_users, ".$prefix."newsletter_drafts2groups, ".$prefix."newsletter_users2groups WHERE ".$prefix."newsletter_drafts2groups.fk_draft = ".$draft." AND ".$prefix."newsletter_users.id=".$prefix."newsletter_users2groups.fk_user AND ".$prefix."newsletter_users2groups.fk_group = ".$prefix."newsletter_drafts2groups.fk_group AND ".$prefix."newsletter_users.activated=1";
        	$users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

			$mailoptions = $this->getMailserverOptions($data['mailserver']);

			// Send it
			$this->sendNewsletter($draft, $users, $mailoptions);   
     	}
     	else if($parts['name'] == "users/.")
     	{
     		if(!empty($_FILES))
     		{
     			// get the content of the uploaded file
     			$file = utf8_encode(file_get_contents($_FILES["userfile"]["tmp_name"]));
     			//bx_helpers_debug::webdump($file);
     			$this->importUsers($file);
     				
     		}
     	}
    }
    
    /**
     * Collection view handler
     * 
     * @return XML response to be sent back to the user
     */
    public function getEditContentById($id) {
     	
     	$parts = bx_collections::getCollectionUriAndFileParts($id);

     	// Manage view requested
     	if($parts['name'] == "manage/")
     	{
     		return $this->generateManageView();
     	}
     	// Send view requested
		else if($parts['name'] == "send/")
		{
     		return $this->generateSendView();
		}
		
     	// Send view requested
		else if($parts['name'] == "users/")
		{
     		return $this->generateUsersView();
		}
    }
    
    /**
     * The send view lets the user enter information about the newsletter to be sent
     */
    protected function generateSendView()
    {
    	// show a list of available groups
 		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select * from ".$prefix."newsletter_groups";
    	$groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

		$groupsHTML = '<select name="groups[]" size="'.count($groups).'" multiple="multiple">';
  		foreach($groups as $row)
  		{
  			$groupsHTML .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
  		}
		$groupsHTML .= '</select>';

		// show a list of newsletter templates created
		$files = $this->getNewsletterFilenames();

		$newsHTML = '<select name="htmlfile" size="1"><option/>';
  		foreach($files as $file)
  		{
  			$newsHTML .= '<option value="'.$file.'">'.$file.'</option>';	
  		}
		$newsHTML .= '</select>';	
		
		// same but for the text version 
		$newsText = '<select name="textfile" size="1"><option/>';
  		foreach($files as $file)
  		{
  			$newsText .= '<option value="'.$file.'">'.$file.'</option>';	
  		}
		$newsText .= '</select>';	

		// get the list of mail servers
		$servers = $this->getMailServers();
		
		$serversHtml = '<select name="mailserver" size="1">';
  		foreach($servers as $server)
  		{
  			$serversHtml .= '<option value="'.$server['id'].'">'.$server['descr'].'</option>';	
  		}
		$serversHtml .= '</select>';				

		$xml = '<newsletter>
    	<form name="bx_news_send" action="#" method="post">
			<table border="0" id="send">
				<tr><td colspan="2"><h3>Send Newsletter</h3></td></tr>
				<tr><td>From:</td><td><input type="text" name="from"/></td></tr>
				<tr><td style="vertical-align:top">To:</td><td>'.$groupsHTML.'</td></tr>
				<tr><td>Subject:</td><td><input type="text" name="subject"/></td></tr>
				<tr><td>HTML Body:</td><td>'.$newsHTML.'</td></tr>
				<tr><td>Text Body:</td><td>'.$newsText.'</td></tr>
				<tr><td>Mail Server:</td><td>'.$serversHtml.'</td></tr>
				<tr>
					<td></td>
					<td><input type="submit" name="bx[plugins][admin_edit][_all]" value="Send" class="formbutton"/></td>
				</tr>
			</table>
		</form>
		</newsletter>';

 		return domdocument::loadXML($xml);    	
    }
    
    /**
     * The manage view shows information about the newsletters created and a more detailed view in case they have already been sent
     */
    protected function generateManageView()
    {
    	// get information about the newsletters sent
		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select * from ".$prefix."newsletter_drafts";
    	$drafts = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

		// get a list of all current newsletter templates
		$newsletters = $this->getNewsletterFilenames();

 		$xml = '<newsletter>
		<h3>Newsletter Archive</h3>
		<form name="bx_news_manage" action="#" method="post">
		<table>
		<cols>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
		</cols>
		<tr>
			<th class="stdBorder">From</th>
			<th class="stdBorder">To</th>
			<th class="stdBorder">Subject</th>
			<th class="stdBorder">HTML Body</th>
			<th class="stdBorder">Text Body</th>
			<th class="stdBorder">Sent</th>
		</tr>';
		
		// the newsletters sent
		foreach($drafts as $row)
		{
	    	$query = "select name from ".$prefix."newsletter_drafts2groups, ".$prefix."newsletter_groups WHERE fk_draft='".$row['id']."' AND fk_group=".$prefix."newsletter_groups.id";
	    	$groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);			
			
	    	$groupstring = "";		
			for($i=0; $i<count($groups); $i++)
			{
				if($i != 0)
					$groupstring .= ", ";
				$groupstring .= $groups[$i]["name"];	
			}
			
			$xml .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', 
							$row['from'], $groupstring, $row['subject'], $row['htmlfile'], $row['textfile'], $row['sent']);
					
			// remove the element if it was already sent from the extra list
			$key = array_search($row['htmlfile'], $newsletters);
			if($key !== null) {
				
				unset($newsletters[$key]);
			}
		} 
		
		// the extra list consists of newsletter template that have not been sent yet
		/*foreach($newsletters as $file)
		{
			$xml .= sprintf('<tr><td>%s</td><td></td><td>never</td></tr>', $file);					
		}*/
		
		$xml .= '</table>
		</form>
		</newsletter>';

 		return domdocument::loadXML($xml);    	
    }
    
    /**
     * The user view shows information about the existing users and lets the admin import a list of new users
     */
    protected function generateUsersView()
    {
    	// get information about the newsletters sent
		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select * from ".$prefix."newsletter_users";
    	$users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

 		$xml = '<newsletter>
		<h3>Newsletter User Management</h3>
		<form enctype="multipart/form-data" name="bx_news_users" action="#" method="post">
		<table>
		<cols>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
			<col width="200"/>
		</cols>
		<tr>
			<th class="stdBorder">Firstname</th>
			<th class="stdBorder">Lastname</th>
			<th class="stdBorder">Gender</th>
			<th class="stdBorder">Email</th>
			<th class="stdBorder">Activated</th>
			<th class="stdBorder">Groups</th>
		</tr>';

		foreach($users as $row)
		{
	    	$query = "select name from ".$prefix."newsletter_users2groups, ".$prefix."newsletter_groups WHERE fk_user='".$row['id']."' AND fk_group=".$prefix."newsletter_groups.id";
	    	$groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	
	    	$groupstring = "";		
			for($i=0; $i<count($groups); $i++)
			{
				if($i != 0)
					$groupstring .= ", ";
				$groupstring .= $groups[$i]["name"];	
			}
			
			$row['activated'] = $row['activated'] == 1 ? "true" : "false";
			$row['gender'] = $row['gender'] == 0 ? "male" : "female";
			
			$xml .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', 
							$row['firstname'], $row['lastname'], $row['gender'], $row['email'], $row['activated'], $groupstring);
		} 

		$xml .= '</table><br/>
		<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>
		<input name="userfile" type="file"/>
		<input type="submit" name="bx[plugins][admin_edit][_all]" value="Import Users" class="formbutton"/>
		</form>
		</newsletter>';

 		return domdocument::loadXML($xml);    	
    }
    
    /**
     * Sends a bunch of mails
     */
    protected function sendNewsletter($draftId, $receivers, $mailoptions)
    {
    	// TODO: this belongs in a separate class for further customization
    	
     	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	
    	
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile']);
		$textMessage = $this->readNewsletterFile($draft['textfile']);

		$htmlMessage = $this->transformHTML($htmlMessage);
		//bx_helpers_debug::webdump($htmlMessage); return;

		// TODO: convert HTML to TXT with Lynx
		// exec( dirname(__FILE__)
		// lynx -force_html -nocolor -dump test.xhtml
	
		$mail_queue =& new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 'From'    => $draft['from']);

		// Iterate over all newsletter receivers
		foreach($receivers as $triple)
		{
			// create the personalized email 
			// The following tags will be replaced with entries from the database:
			//		{firstname}, {lastname}, {email}, {title}
			$customHtml = $this->customizeMessage($htmlMessage, $triple);
			$customText = $this->customizeMessage($textMessage, $triple);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			$mime =& new Mail_mime();
			if($textMessage !== false)
				$mime->setTXTBody(utf8_decode($customText));
			if($htmlMessage !== false)
				$mime->setHTMLBody(utf8_decode($customHtml));
			$body = $mime->get();
			$hdrs = $mime->headers($hdrs);								
			$hdrs['Subject'] = $draft['subject'];
			$hdrs['To'] = $triple['email'];
			
			// Generate a special bounce address e.g. fluxcms-bounces+milo=bitflux.ch@bitflux.ch
			$bounceEmail = str_replace("@", "=", $triple['email']);
			$hdrs['Return-Path'] = "fluxcms-bounces+".$bounceEmail."@bitflux.ch";
			
			// Put it in the queue (the message will be cached in the database)
			$mail_queue->put($hdrs['From'], $triple['email'], $hdrs, $body );
		}
		
		// TODO: this could be called from a CRON-job
		// finally send the messages
		$max_amount_mails = 100;
		$mail_queue->sendMailsInQueue($max_amount_mails);	
    }
    
    /**
     * Add custom style to the HTML document to replace the missing .css style sheet
     */
    protected function transformHTML($inputMessage)
    {
		$xsl = new DomDocument();
		$xsl->load('data/newsletter/transform.xsl');
		$inputdom = new DomDocument();
		$inputdom->loadXML($inputMessage);
		$proc = new XsltProcessor();
		$xsl = $proc->importStylesheet($xsl);
		$newdom = $proc->transformToDoc($inputdom);
		return $newdom->saveXML();    	
    }
    
    /**
     * Customized the message for a certain user
     */
    protected function customizeMessage($message, $parameters)
    {
			$title = $parameters['gender'] == '0' ? 'Herr' : 'Frau';
			return str_replace(array('{firstname}', '{lastname}', '{email}', '{title}'), 
										array($parameters['firstname'], $parameters['lastname'], $parameters['email'], $title), $message);  	
    }

    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name)
    {
    	return file_get_contents('data/newsletter/'.$name);
    }
    
    /**
     * Gets all newsletters saved in the collection
     */
    protected function getNewsletterFilenames()
    {
    	$newsletters = array();
    	$counter = 0;
    	$files = scandir("data/newsletter/");
    	
    	foreach($files as $file)
    	{
    		// skip directories and config files
    		if(strncmp($file,".",1)	!= 0)
    			if(strncmp($file,"index",5)	!= 0)
    			{
    				$newsletters[$counter++] = $file;
    			}
    	}
    	
    	return $newsletters;
    }
    
    /**
     * Imports a list of new users as comma separated values (CSV)
     * Line format: firstname,lastname,gender(male|female),email
     */
    protected function importUsers($file)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	
    	$lines = explode("\n", $file);
    	foreach($lines as $line)
    	{
    		$tokens = explode(",", $line);
    		
    		$tokens[2] = $tokens[2] == "male" ? 0 : 1;
    		
    		// check if the email address is set at least
    		if(isset($tokens[3]))
    		{
	        	$query = "insert into ".$prefix."newsletter_users (firstname, lastname, gender, email) value('".$tokens[0]."', '".$tokens[1]."', '".$tokens[2]."', '".$tokens[3]."')";
	        	$GLOBALS['POOL']->dbwrite->query($query);
    		}
    	}
    }
    
	/**
	 * Returns an array of newsletter mail servers
	 * 
	 * @return associated array
	 */
	protected function getMailServers()
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_mailservers";
        $res = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	
        return $res;		
	}
	
	/**
	 * Creates the mail_options structure for sending mails over Mail_Queue
	 */
	protected function getMailserverOptions($id)
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_mailservers where id=".$id;
        $server = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);	
        
		$mail_options = array();
		$mail_options['driver']    = 'smtp';
		$mail_options['host']      = $server['host'];
		$mail_options['port']      = $server['port'];
		$mail_options['auth']      = empty($server['username']) ? false : true;
		$mail_options['username']  = $server['username'];
		$mail_options['password']  = $server['password'];	
		//$mail_options['localhost'] = 'localhost'; //optional Mail_smtp parameter
		
		return $mail_options;
	}
}

?>
