<?php

/**
 * Creates and manages newsletters
 */
class bx_editors_newsletter extends bx_editor implements bxIeditor {    
    
    protected $db_options;
    protected $mail_options;
    
    /**
     * Configure mail server options
     */
    public function __construct()
    {
		// options for storing the messages
		// type is the container used, currently there are db and mdb available
		$this->db_options['type']       = 'db';
		// the others are the options for the used container
		// here are some for db
		$this->db_options['dsn']        = 'mysql://fluxcms:fluxcms@localhost/fluxcms';
		$this->db_options['mail_table'] = 'fluxcms_mail_queue';
		
		// here are the options for sending the messages themselves
		// these are the options needed for the Mail-Class, especially used for Mail::factory()
		$this->mail_options['driver']    = 'smtp';
		$this->mail_options['host']      = 'mail.bitflux.ch';
		$this->mail_options['port']      = 25;
		$this->mail_options['localhost'] = 'localhost'; //optional Mail_smtp parameter
		$this->mail_options['auth']      = false;
		$this->mail_options['username']  = '';
		$this->mail_options['password']  = '';		
    }
    
    public function getDisplayName() {
        return "Newsletter";
    }

	/** bx_editor::getPipelineParametersById */
	public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'newsletter');
    }
    
    /**
     * Collect a list of email addresses from the selected groups
     */
    public function handlePOST($path, $id, $data) {
 		
 		$parts = bx_collections::getCollectionUriAndFileParts($id);
 		
     	// send newsletter
     	if($parts['name'] == "show/.")
     	{
     		foreach($data as $key => $value)
     			$draft = $key;

     		// get a unique list of people to receivers
     		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
     		
     		$query = "SELECT DISTINCT firstname, lastname, email FROM ".$prefix."newsletter_users, ".$prefix."newsletter_receivers, ".$prefix."newsletter_lists WHERE ".$prefix."newsletter_receivers.fk_draft = ".$draft." AND ".$prefix."newsletter_users.id=".$prefix."newsletter_lists.fk_user AND ".$prefix."newsletter_lists.fk_group = ".$prefix."newsletter_receivers.fk_group";
     		
        	$users = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	     		

			$this->sendNewsletter($draft, $users);
			
			$GLOBALS['POOL']->db->query("UPDATE ".$prefix."newsletter_drafts SET sent = NOW() WHERE id = ".$draft);
     	}
     	else if($parts['name'] == "create/.")
     	{
     		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
     		$query = 	"INSERT INTO ".$prefix."newsletter_drafts (`from`,`subject`,`htmlfile`,`txtfile`)
						VALUES (
						'".$data['from']."', '".$data['subject']."', 'cssmail.xhtml', 'textmail.txt');";
			$GLOBALS['POOL']->db->query($query);
			
			$draft = $GLOBALS['POOL']->db->queryOne("SELECT ID FROM ".$prefix."newsletter_drafts ORDER BY ID DESC LIMIT 1;");
			
			foreach($data['groups'] as $grp)
			{
				$query = 	"INSERT INTO ".$prefix."newsletter_receivers (`fk_draft`,`fk_group`)
							VALUES (
							'".$draft."', '".$grp."');";
				$GLOBALS['POOL']->db->query($query);
			}
     	}
    }
    
    /**
     * Shows a form the select and send a newsletter
     */
    public function getEditContentById($id) {
     	
     	$parts = bx_collections::getCollectionUriAndFileParts($id);
     	
     	// show a list of newsletters
     	if($parts['name'] == "show/")
     	{
     		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
        	$query = "select * from ".$prefix."newsletter_drafts";
        	$drafts = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

     		$xml = '<newsletter>
			<h1>Newsletter list</h1>
			<form name="bx_news_send" action="#" method="post">
			<table border="1">
			<tr>
				<th>Subject</th>
				<th>Created</th>
				<th>Sent</th>
				<th></th>
			</tr>';
			
			foreach($drafts as $row)
			{
				$xml .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td><input type="submit" name="bx[plugins][admin_edit][%s]" value="Send" class="formbutton"/></td></tr>', 
								$row['subject'], $row['created'], $row['sent'], $row['id']);
			} 
			
			$xml .= '</table>
			</form>
			</newsletter>';

     		return domdocument::loadXML($xml);
     	}
     	// create a new newsletters
		else if($parts['name'] == "create/")
		{
     		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
        	$query = "select * from ".$prefix."newsletter_groups";
        	$groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);	

     		$xml = '<newsletter>
			<h1>Create</h1>
			<form name="bx_news_send" action="#" method="post">
			From: <input type="text" name="from"/><br/>';
 
    		$xml .= 'To: <select name="groups[]" size="5" multiple="multiple">';

      		foreach($groups as $row)
      		{
      			$xml .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';	
      		}

			$xml .= '</select><br/>
			Subject: <input type="text" name="subject"/><br/>
			<a href="">Edit HTML</a><br/>
			<a href="">Edit Text</a><br/>
			<input type="submit" name="bx[plugins][admin_edit][_all]" value="Entwurf speichern" class="formbutton"/>
			</form>
			</newsletter>';

     		return domdocument::loadXML($xml);
		}
    }
    
    /**
     * Enumerate over all users on the newsletter list and generate a personalized message
     */
    protected function sendNewsletter($draftId, $receivers)
    {
     	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $draft = $GLOBALS['POOL']->db->queryAll("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	
    	
    	$htmlMessage = file_get_contents('files/_newsletter/'.$draft[0]['htmlfile']);
		$textMessage = file_get_contents('files/_newsletter/'.$draft[0]['txtfile']);
	
		/* we use the db_options and mail_options here */
		$mail_queue =& new Mail_Queue($this->db_options, $this->mail_options);
		
		$hdrs = array( 'From'    => $draft[0]['from']);
		
		/* we use Mail_mime() to construct a valid mail */
		$mime =& new Mail_mime();
		$mime->setTXTBody($textMessage);
		$mime->setHTMLBody($htmlMessage);
		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		/* Put messages to queue */
		foreach($receivers as $triple)
		{
			$hdrs['Subject'] = $draft[0]['subject'];
			$hdrs['To'] = $triple['email'];
			$mail_queue->put($hdrs['From'], $triple['email'], $hdrs, $body );
		}
		
		/* How many mails could we send each time the script is called */
		$max_amount_mails = 1000;
		
		/* really sending the messages */
		$mail_queue->sendMailsInQueue($max_amount_mails);	
		$mail_queue->sendMailsInQueue($max_amount_mails);	
    }
}

?>
