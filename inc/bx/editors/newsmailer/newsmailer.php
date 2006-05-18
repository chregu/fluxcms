<?php

/**
 * Factory and default class to send newsletters (using Mail_Queue)
 */
class bx_editors_newsmailer_newsmailer {    
    
    protected static $htmlImages = array();
    protected $db_options;
    
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
		$class = new ReflectionClass("bx_editors_newsmailer_".$name);
		return $class->newInstance();
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
     * Sends a bunch of mails
     */
    public function sendNewsletter($draft, $receivers, $mailoptions, $embedImages = false)
    {
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile']);
		$textMessage = $this->readNewsletterFile($draft['textfile']);

		$dom = new DomDocument();
		$dom->loadXML($htmlMessage);
		
		if($embedImages) {
			self::$htmlImages = array();
			$dom = $this->transformHTMLImages($dom);
    	}
		$dom = $this->transformHTML($dom);

		$htmlTransform = $dom->saveXML();

		// TODO: convert HTML to TXT with Lynx
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 'From'    => $draft['from']);

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person);
			$customText = $this->customizeMessage($textMessage, $person);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			$mime =& new Mail_mime();
			if($textMessage !== false)
				$mime->setTXTBody(utf8_decode($customText));
			if($htmlMessage !== false)
				$mime->setHTMLBody(utf8_decode($customHtml));
				
			if($embedImages) {
				// Add images to MIME Body
				foreach(self::$htmlImages as $image) {
					$type = end(explode(".", $image["name"]));
					$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
				}
			}

			$body = $mime->get();
			$hdrs = $mime->headers($hdrs);								
			$hdrs['Subject'] = $draft['subject'];
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
		$xsl->load('themes/3-cols/htmlimage.xsl');
		$proc = new XsltProcessor();
		$proc->registerPHPFunctions();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
    
    /**
     * Customized the message for a certain user
     */
    protected function customizeMessage($message, $person)
    {
    	$templates = array();
    	$values = array();
    	foreach($person as $key=>$val) {
    		array_push($templates, '{' . $key . '}');
    		array_push($values, $val);
    	}
    	
    	array_push($templates, '{title}');
    	array_push($values, $person['gender'] == '0' ? 'Mr' : 'Ms');
    	
    	return str_replace($templates, $values, $message);
    }

    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name)
    {
    	return file_get_contents('data/newsletter/'.$name);
    }
    
    /**
     * Creates the bounce (return-path) address for the message
     */
    protected function getBounceAddress($parameters)
    {
		// Generate a special bounce address e.g. fluxcms-bounces+milo=bitflux.ch@bitflux.ch
		$bounceEmail = str_replace("@", "=", $parameters['email']);
		return "fluxcms-bounces+".$bounceEmail."@bitflux.ch";    	
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
