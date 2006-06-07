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
		$this->db_options['type']       = 'db';
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
     * Creates all the MIME mail bodies for the selected newsletter and saves them into the mail queue
     */
    public function autoPrepareNewsletter($draftId)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		$query = "SELECT DISTINCT u.* FROM ".$prefix."newsletter_cache c, ".$prefix."newsletter_users u WHERE c.fk_draft=".$draftId." AND c.fk_user=u.id";
    	$receivers = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
    	
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    	// load baseurl from db, we are not running inside apache!
    	$this->baseUrl = $draft["baseurl"];
    	
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

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile']);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile']);
			
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
			$mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
			
	    	$query = "UPDATE ".$prefix."newsletter_cache SET status='2' WHERE fk_user='".$person['id']."' AND fk_draft='".$draftId."'";
	    	$GLOBALS['POOL']->dbwrite->exec($query);
		}

		// all mails for this draft were preprocessed successfully
    	$query = "UPDATE ".$prefix."newsletter_drafts SET prepared=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
    
    	$query = "DELETE FROM ".$prefix."newsletter_cache WHERE fk_draft=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
    }
    
    /**
     * Send all the mails in the queue at once
     */
    public function autoSendNewsletter($draftId)
    {
    	sleep(1);
    	
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue();	
		
    	$query = "UPDATE ".$prefix."newsletter_drafts SET sent=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
		
		return !$mail_queue->isError($retval);    	
    }
    
    /**
     * Sends a bunch of mails
     */
    public function sendNewsletter($draft, $receivers, $mailoptions, $embedImages = false)
    {
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile'], "html");
		$textMessage = $this->readNewsletterFile($draft['textfile'], "text");

		$dom = new DomDocument();
		
		if(!empty($draft['htmlfile'])) {
			$dom->loadXML($htmlMessage);
			
			$dom = $this->transformHTML($dom);
			
			if($embedImages) {
				self::$htmlImages = array();
				$dom = $this->transformHTMLImages($dom);
	    	}
		}

		$htmlTransform = $dom->saveXML();
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 	'From'    => $draft['from'],
						'Subject' => $draft['subject'] );

		$mime =& new Mail_mime();

		if($embedImages) {
			// Add images to MIME Body
			foreach(self::$htmlImages as $image) {
				$type = end(explode(".", $image["name"]));
				$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
			}
		}
		
		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			$start_time_test = time() + microtime(true);
			
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile']);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile']);
			
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
			$mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
		}

		// wait a second before we send, otherwise the queue seems to be empty (bug)
    	sleep(1);
		
		$this->finalizeSend($mailoptions);
    }
    
    /**
     * If double-opt-in is set, send the user an email in order to confirm his subscription
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
     */
    public function finalizeSend($mailoptions, $maxAmount = 1000)
    {
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue($maxAmount);	
		return !$mail_queue->isError($retval);
    }
    
    /**
     * Add custom style to the HTML document
     */
    protected function transformHTML($inputdom)
    {
		return $inputdom;   	  	
    }
    
    /**
     * Embedds images directly into the HTML document
     */
    protected function transformHTMLimages($inputdom)
    {
		$xsl = new DomDocument();
		$xsl->load('themes/'.bx_helpers_config::getTheme().'/htmlimage.xsl');
		$proc = new XsltProcessor();
		$proc->registerPHPFunctions();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
    
    /**
     * Customized the message for a certain user
     * tags in the form of {field} are replaced with its corresponding value from the database
     */
    protected function customizeMessage($message, $person, $filename)
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
    	
    	$webfilename = str_replace(array('en.xhtml', 'de.xhtml'), 'html', $filename);

    	array_push($templates, '{weblink}', '{activate}', '{unsubscribe}', '{publication}', '{date}');
    	
    	//array_push($values, $person['gender'] == '0' ? 'Herr' : 'Frau');
    	array_push($values, $this->baseUrl);
    	array_push($values, $this->baseUrl."newsletter/index.html?activate=".$person['activation']);
    	array_push($values, $this->baseUrl."newsletter/index.html?unsubscribe=".$person['email']);
    	array_push($values, $this->baseUrl."newsletter/archive/".$webfilename);
    	array_push($values, date("m/Y"));
    	
    	return str_replace($templates, $values, $message);
    }

    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name, $type)
    {
    	if(($content = @file_get_contents('data/newsletter/archive/'.$name)) == false) {
    		$content = @file_get_contents('data/newsletter/'.$name);
    	}
    	return $content;
    }
    
    /**
     * Creates the bounce (return-path) address for the message
     */
    protected function getBounceAddress($parameters)
    {
		// Generate a special bounce address e.g. bounces+milo=bitflux.ch@bitflux.ch
		$bounceEmail = str_replace("@", "=", $parameters['email']);
		return "bounces+".$bounceEmail."@bitflux.ch";    	
    }
    
    /**
     * Callback function from htmlimage.xsl
     * Preloads the images found and returns a short filename in order to reference them as embedded HTML images
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
