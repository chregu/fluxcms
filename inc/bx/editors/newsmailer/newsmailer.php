<?php

/**
 * Factory and default class to send newsletters (using Mail_Queue)
 */
class bx_editors_newsmailer_newsmailer {    
    
    protected static $htmlImages = array();
    protected $db_options;
    protected $baseUrl = BX_WEBROOT;
    
    /**
     * Configure Mail_Queue database options
     */
    public function __construct()
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		$this->db_options['type']       = 'mdb2';
		$this->db_options['dsn']        = $GLOBALS['POOL']->dbwrite->getDSN();
		$this->db_options['mail_table'] = $prefix.'mail_queue';	
    }
    
    /**
     * Factory to create an instance of a newsmailer from its name
     */
    final public static function newsMailerFactory($name)
    {
    	$module = "bx_editors_newsmailer_".$name;
    	if(class_exists($module)) {
			return new $module();
        }
		return null;
    }
    
	/**
	 * Creates the mail_options structure for sending mails over Mail_Queue
	 * @param id newsletter_mailserver id
	 * @return array with mail_queue options
	 */
	final public static function getMailserverOptions($id)
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
    
    /**
     * Creates all the MIME mail bodies for the selected newsletter and saves them into the mail queue table
     * @param draftId newsletter_drafts id 
     */
    public function autoPrepareNewsletter($draftId)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    //mark as prepareds    
    $query = "UPDATE ".$prefix."newsletter_drafts SET prepared=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);    
	$query = "SELECT DISTINCT u.* FROM ".$prefix."newsletter_cache c, ".$prefix."newsletter_users u WHERE c.fk_draft=".$draftId." AND c.fk_user=u.id";
    	$receivers = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
    	
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    	// load baseurl from db, we are not running inside apache!
    	$this->baseUrl = $draft["baseurl"];
    	// read in the newsletter templates if existing
    	   $htmlMessage = $this->readNewsletterFile($draft['htmlfile'], "html", $draft['colluri']);
		$textMessage = $this->readNewsletterFile($draft['textfile'], "text", $draft['colluri']);

		$dom = new DomDocument();
		
		if(!empty($draft['htmlfile'])) {
			$dom->loadXML($htmlMessage);
			
			$dom = $this->transformHTML($dom);
			
			if($draft["embed"] == 1) {
				self::$htmlImages = array();
				$dom = $this->transformHTMLImages($dom);
            } else {
                $dom = $this->transformHTMLImagesLinks($dom,$draft['baseurl']);
            }
		}

		$htmlTransform = $dom->saveXML();
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 	'From'    => $draft['from'],
						'Subject' => $draft['subject'] );

		$mime =& new Mail_mime();

		if($draft["embed"] == 1) {
			// Add images to MIME Body
			foreach(self::$htmlImages as $image) {
				$type = end(explode(".", $image["name"]));
				$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
			}
		}
		
		if(!empty($draft['attachment'])) {
			$path = ltrim($draft['attachment'], "/");
			if(($attachment = @file_get_contents($path)) == true) {
    			$shortname = end(preg_split("[\\/]", $path));
				$mime->addAttachment($attachment, 'application/octet-stream', $shortname, false);	
			}
		}

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile'],$draft);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile'],$draft);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			if(!empty($draft['textfile']))
				$mime->setTXTBody(utf8_decode($customText));
			if(!empty($draft['htmlfile']))
				$mime->setHTMLBody($customHtml);

			$params = array('text_encoding' => 'base64',
                            'html_encoding' => 'quoted-printable');

			$body = $mime->get($params);
			$hdrs = $mime->headers($hdrs);			
			$hdrs['To'] = $person['email'];
			$hdrs['Return-Path'] = $this->getBounceAddress($person);

			// Put it in the queue (the message will be cached in the database)
		   $ret = $mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
           if (PEAR::isError($ret)) {
                var_dump($ret->getMessage() . "\n".$ret->getUserInfo());   
            }
	    	$query = "UPDATE ".$prefix."newsletter_cache SET status='2' WHERE fk_user='".$person['id']."' AND fk_draft='".$draftId."'";
	    	$GLOBALS['POOL']->dbwrite->exec($query);
		}

		// all mails for this draft were preprocessed successfully

    
    	$query = "DELETE FROM ".$prefix."newsletter_cache WHERE fk_draft=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
    }
    
    /**
     * Send all the mails in the queue at once
     * @param draftId newsletter_drafts id which is needed to update the timestamps of the drafts sent
     */
    public function autoSendNewsletter($draftId)
    {
    	sleep(1);
    	
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    $query = "UPDATE ".$prefix."newsletter_drafts SET sent=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
	
        
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue();	
		
    		
		return !$mail_queue->isError($retval);    	
    }
    
    /**
     * Sends mails directly without waiting for the cronjob
     * @param draft array with a newsletter_drafts row
     * @param receivers array newsletter_users entries
     * @param mailoptions return value of getMailserverOptions()
     * @param embedImages if true all images in the HTML document (src/background attributes) will be sent as attachments
     */
    public function sendNewsletter($draft, $receivers, $mailoptions)
    {
    	// read in the newsletter templates if existing
        
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile'], "html");
		$textMessage = $this->readNewsletterFile($draft['textfile'], "text");

		$dom = new DomDocument();
		
		if(!empty($draft['htmlfile'])) {
			$dom->loadXML($htmlMessage);
			
			$dom = $this->transformHTML($dom);
			
		if($draft["embed"] == 1) {
				self::$htmlImages = array();
				$dom = $this->transformHTMLImages($dom);
	    	}
		}

		$htmlTransform = $dom->saveXML();
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 	'From'    => $draft['from'],
						'Subject' => $draft['subject'] );

		$mime =& new Mail_mime();

		if($draft["embed"] == 1) {
			// Add images to MIME Body
			foreach(self::$htmlImages as $image) {
				$type = end(explode(".", $image["name"]));
				$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
			}
		}
		
		if(!empty($draft['attachment'])) {
			$path = ltrim($draft['attachment'], "/");
			if(($attachment = @file_get_contents($path)) == true) {
    			$shortname = end(preg_split("[\\/]", $path));
				$mime->addAttachment($attachment, 'application/octet-stream', $shortname, false);	
			}
		}
		
		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile'],$draft);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile'],$draft);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			if(!empty($draft['textfile']))
				$mime->setTXTBody(utf8_decode($customText));
			if(!empty($draft['htmlfile']))
				$mime->setHTMLBody($customHtml);

			// b64 encode the text body to preserve linebreaks
			$params = array('text_encoding' => 'base64',
                            'html_encoding' => 'quoted-printable');

			// this call is time consuming because it aggregates the MIME parts 
			$body = $mime->get($params);
			
			$hdrs = $mime->headers($hdrs);			
	
			$hdrs['To'] = $person['email'];
			$hdrs['Return-Path'] = $this->getBounceAddress($person);

			// Put it in the queue (the message will be cached in the database)
			$mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
		}

		// wait a second before we send, otherwise the queue seems to be empty (bug)
    	sleep(1);
		
		$this->finalizeSend($mailoptions);
    }
    
    /**
     * If double-opt-in is set, send the user an email in order to confirm his subscription
     * @param person see sendNewsletter()
     * @param mailserver name of the mailserver to be used from newsletter_mailservers
     * @param mailfrom email address to be used as sender
     * @param mailsub mail subject
     * @param mailtext text body filename (to be located in the newsletter directory)
     * @param mailhtml html body filename (to be located in the newsletter directory)
     */
    public function sendActivationMail($person, $mailserver, $mailfrom, $mailsub, $mailtext, $mailhtml)
    {
    	if($mailserver === null) {
			$mailserver = "default";
    	}

		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select id from ".$prefix."newsletter_mailservers where descr='".$mailserver."'";
    	$mailserver = $GLOBALS['POOL']->db->queryOne($query);			

		$options = $this->getMailserverOptions($mailserver);
		
		$draft = array(	"from" => $mailfrom,
						"subject" => $mailsub,
						"textfile" => $mailtext,
						"htmlfile" => $mailhtml);

		$this->sendNewsletter($draft, array($person), $options);
    }
    
    /**
     * Is called to indicate all messages are saved in the queue
     * @param mailoptions see getMailserverOptions()
     * @param maxAmount sends up to 1000 emails (takes around 15 minutes)
     * @return true if all mails could be sent
     */
    public function finalizeSend($mailoptions, $maxAmount = 1000)
    {
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue($maxAmount);	
		return !$mail_queue->isError($retval);
    }
    
    /**
     * Add custom style to the HTML document
     * @param inputdom DomDocument
     * @return transformed DocDocument
     */
    public function transformHTML($inputdom)
    {
		return $inputdom;   	  	
    }
    
    /**
     * Collects the names and paths of the images embedded in the HTML document
     * @param inputdom DomDocument
     * @return transformed DocDocument
     */
    protected function transformHTMLimages($inputdom)
    {
		$xsl = new DomDocument();
		//$xsl->load('themes/'.bx_helpers_config::getTheme().'/htmlimage.xsl');
		$xsl->load('themes/standard/plugins/newsletter/htmlimage.xsl');
		$proc = new XsltProcessor();
		$proc->registerPHPFunctions();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
    
    protected function transformHTMLimagesLinks($inputdom,$webroot)
    {
		$xsl = new DomDocument();
		//$xsl->load('themes/'.bx_helpers_config::getTheme().'/htmlimage.xsl');
		$xsl->load('themes/standard/plugins/newsletter/htmlimagelinks.xsl');
		$proc = new XsltProcessor();
		$proc->registerPHPFunctions();
		$xsl = $proc->importStylesheet($xsl);
        $proc->setParameter('','webroot',$webroot);
		return $proc->transformToDoc($inputdom);  	
    }
    
    
    /**
     * Customized the message for a certain user
     * - {field} is replaced with its corresponding value from the database
     * - {m|f:text} is only inserted if the gender of the user is matching
     * - {weblink} link to the website base direcotry
     * - {activate} user's subscription activation link
     * - {unsubscribe} user's unsubscription link
     * - {publication} archive link for this newsletter
     * - {date} current date as MM/YYYY
     * 
     * @param message string with message
     * @param person newsletter_users entry
     * @param filename html newsletter filename 
     * @return customized message
     */
    protected function customizeMessage($message, $person, $filename,$draft)
    {
    	$templates = array();
    	$values = array();
    	foreach($person as $key=>$val) {
    		array_push($templates, '{' . $key . '}');
    		array_push($values, $val);
    	}

		// replace templates in the form {m|f:Text} with the included text in case 
		// the gender matches the condition
		if($person['gender'] == '0') {
			$replace = array("$2", "");
		} else {
			$replace = array("", "$2");	
		}
    	$message = preg_replace(array("/{([m]{1}):([^\}]*)}/", "/{([f]{1}):([^\}]*)}/"), 
									$replace, 
									$message);  
    	
    	// remove language code from filename
    	$webfilename = preg_replace('/(.*).(.{2}).xhtml/', '\1.html', $filename);

    	array_push($templates, '{weblink}', '{activate}', '{unsubscribe}', '{publication}', '{date}');
    	
    	//array_push($values, $person['gender'] == '0' ? 'Herr' : 'Frau');
    	array_push($values, $this->baseUrl);
    	array_push($values, $this->baseUrl.$draft['colluri']."index.html?activate=".$person['activation']);
    	array_push($values, $this->baseUrl.$draft['colluri']."index.html?unsubscribe=".$person['email']."&groups=".$draft['group']);
	if (strpos($webfilename,"draft") === false) {
		$webfilename = "archive/$webfilename";
	}
    	array_push($values, $this->baseUrl.$draft['colluri'].$webfilename);
    	array_push($values, date("m/Y"));
    	
    	return str_replace($templates, $values, $message);
    }

    /**
     * Reads a newsletter resource file and returns its content
     * @param name filename to be read
     * @param type either text or html
     * @return file content in a string
     */
    protected function readNewsletterFile($name, $typem, $colluri)
    {
    	// normally the file is in the archive directory but activation e.g. is in the newsleter base directory
    	if(($content = @file_get_contents('data'.$colluri.'archive/'.$name)) == false) {
    		$content = @file_get_contents('data'.$colluri.$name);
    	}
    	return $content;
    }
    
    /**
     * Creates the bounce (return-path) address for the message
     * @param parameters newsletter_users entry
     * @return bounce address for this email
     */
    protected function getBounceAddress($parameters)
    {
		// Generate a special bounce address e.g. bounces+milo=bitflux.ch@liip.ch
		$bounceEmail = str_replace("@", "=", $parameters['email']);
		return "bounces+".$bounceEmail."@liip.ch";    	
    }
    
    /**
     * Callback function from htmlimage.xsl
     * Preloads the images found and returns a short filename in order to reference them as embedded HTML images
     * @param path image location
     * @return filename without a path needed to create the content-id (CID)
     */
    public static function adjustImagePath($path)
    {
    	// extract filename
    	$path = ltrim($path, "/");
    	$shortname = end(preg_split("[\\/]", $path));	
    	
    	if(($content = file_get_contents($path)) != false) {
    		
    		// save the loaded image, this array is being read by sendNewsletter() later
    		self::$htmlImages[] = array("name" => $shortname, "content" => $content);
    	}
    	
    	return $shortname;
    }
}

?>
