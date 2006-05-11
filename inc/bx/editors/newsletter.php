<?php

/**
 * Admin view of the newsletter collection
 */
class bx_editors_newsletter extends bx_editor implements bxIeditor {    
    
    protected $callbackDate = "00000000000000";
    
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
			
			$draftId = $GLOBALS['POOL']->dbwrite->queryOne("SELECT ID FROM ".$prefix."newsletter_drafts ORDER BY ID DESC LIMIT 1;");
			
			$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	
			
			foreach($data['groups'] as $grp)
			{
				$query = 	"INSERT INTO ".$prefix."newsletter_drafts2groups (`fk_draft`,`fk_group`)
							VALUES (
							'".$draftId."', '".$grp."');";
				$GLOBALS['POOL']->dbwrite->query($query);
			}
			
			// Get a unique list of subscriptors
			$query = "SELECT DISTINCT firstname, lastname, email, gender, activated FROM ".$prefix."newsletter_users, ".$prefix."newsletter_drafts2groups, ".$prefix."newsletter_users2groups WHERE ".$prefix."newsletter_drafts2groups.fk_draft = ".$draftId." AND ".$prefix."newsletter_users.id=".$prefix."newsletter_users2groups.fk_user AND ".$prefix."newsletter_users2groups.fk_group = ".$prefix."newsletter_drafts2groups.fk_group AND ".$prefix."newsletter_users.activated=1";
        	$users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

			// get news mailer instance
			$classname = $this->getConfigParameter($id, "sendclass");
			$newsmailer = bx_editors_newsmailer_newsmailer::newsMailerFactory($classname);

			// Send it
			$mailoptions = bx_editors_newsmailer_newsmailer::getMailserverOptions($data['mailserver']);
			$newsmailer->sendNewsletter($draft, $users, $mailoptions);   
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
     	else if($parts['name'] == "feed/.")
     	{
     		// Generate a newsletter from a RSS feed
     		$this->createFromFeed($data);	
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
		
     	// Send view requested
		else if($parts['name'] == "feed/")
		{
     		return $this->generateFeedView();
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
		<h3>User Management</h3>
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
     * The feed view lets the user generate a html newsletter from a RSS feed
     */
    protected function generateFeedView()
    {
    	// show a list of available feeds
 		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select * from ".$prefix."newsletter_feeds";
    	$feeds = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

		$feedsHTML = '<select name="feed" size="1">';
  		foreach($feeds as $feed)
  		{
  			$feedsHTML .= '<option value="'.$feed["id"].'">'.$feed["name"].'</option>';	
  		}
		$feedsHTML .= '</select>';	
					

		$xml = '<newsletter>
    	<form name="bx_news_send" action="#" method="post">
			<table border="0" id="send">
				<tr><td colspan="2"><h3>Generate Newsletter from RSS Feed</h3></td></tr>
				<tr><td>Feed:</td><td>'.$feedsHTML.'</td></tr>
				<tr>
					<td></td>
					<td><input type="submit" name="bx[plugins][admin_edit][_all]" value="Generate" class="formbutton"/></td>
				</tr>
			</table>
		</form>
		</newsletter>';

 		return domdocument::loadXML($xml);    		
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
    	
    	// replace different line delimiters
    	$file = str_replace(array("\r\n", "\r"), "\n", $file);
    	
    	$lines = explode("\n", $file);
    	foreach($lines as $line)
    	{
    		$tokens = explode(",", $line);

    		$tokens[2] = ($tokens[2][0] == 'm' ? 0 : 1);
    		
    		// check if the email address is set at least
    		if(isset($tokens[3]))
    		{
	        	$query = "insert into ".$prefix."newsletter_users (firstname, lastname, gender, email) value('".$tokens[0]."', '".$tokens[1]."', '".$tokens[2]."', '".$tokens[3]."')";
	        	$GLOBALS['POOL']->dbwrite->query($query);
    		}
    	}
    }
    
    /**
     * Generates a HTML newsletter file from the given RSS feed. Only new entries since the feed was read last
     * time are included in the message.
     */
    protected function createFromFeed($data)
    {
 		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "SELECT * FROM ".$prefix."newsletter_feeds WHERE id=".$data["feed"];
    	$feed = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);	    	

    	// use html special characters so the code can be displayed later
    	$feedContent = file_get_contents($feed["url"]);
    	$feedContent = str_replace(array('&lt;', '&gt;'), array('<', '>'), $feedContent);
    	
    	// simplify date format (YYYYMMDDhhmmss) so we can compare it later within Xslt
		$this->callbackDate = $feed["lastdate"];
		$feedContent = preg_replace("/<dc:date>(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z<\/dc:date>/e", 
									"'<dc:date>'.\$this->callbackFeedDate('$1$2$3$4$5$6').'</dc:date>'", 
									$feedContent);
 
		$xsl = new DomDocument();
		$xsl->load('themes/3-cols/newsfeeds.xsl');
		$inputdom = new DomDocument();
		$inputdom->loadXML($feedContent);
		$proc = new XsltProcessor();
		$xsl = $proc->importStylesheet($xsl);
		$proc->setParameter('', 'lastdate', $feed["lastdate"]);
		$newdom = $proc->transformToDoc($inputdom);
		
		// save newsletter file
		$filenameSimple = $feed["name"].'_'.date("Ymd-His");
		$filename = $filenameSimple.'.en.xhtml';
		$newdom->save('data/newsletter/'.$filename);
		
		// add as resource so it's visible inside the collection
		bx_resourcemanager::setProperty("/newsletter/".$filename, "parent-uri", "/newsletter/");
		bx_resourcemanager::setProperty("/newsletter/".$filename, "display-name", $filenameSimple);
		bx_resourcemanager::setProperty("/newsletter/".$filename, "display-order", "0");
		bx_resourcemanager::setProperty("/newsletter/".$filename, "mimetype", "text/html");
		bx_resourcemanager::setProperty("/newsletter/".$filename, "output-mimetype", "text/html");
		
		// update table with the date of the most recent feed entry
    	$query = "UPDATE ".$prefix."newsletter_feeds SET lastdate='".$this->callbackDate."' WHERE id=".$data["feed"];
    	$GLOBALS['POOL']->dbwrite->query($query);	 		
    }
    
    /**
     * This callback function is invoked by createFromFeed in order to extract the date of the most recent feed entry
     */
    protected function callbackFeedDate($date)
    {
    	// if the new date is greater save it
    	if($this->callbackDate < $date) {
    		$this->callbackDate = $date;
    	}
    	
    	return $date;
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
	 * Gets a value from the .configxml
	 */
	protected function getConfigParameter($id, $name)
	{
	    $collection = bx_collections::getCollection($id);
     	$plugins = $collection->getPluginMapByRequest($id);
     	foreach($plugins as $p ) {
     		if ($p['plugin']->name == 'newsletter') {
     			$plugin = $p['plugin'];
     			break;
     		}
     	}
     	return $plugin->getParameter($collection->uri,$name);	
	}
}

?>
