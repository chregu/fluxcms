<?php

/**
 * Factory and default class to send newsletters (using Mail_Queue)
 */
class bx_editors_newsmailer_newsmailer {    
    
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
	 * Returns the id of the mailserver with the name 'default'
	 */
	final public static function getDefaultMailServer()
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select id from ".$prefix."newsletter_mailservers where descr='default'";
        return $GLOBALS['POOL']->db->queryOne($query);			
	}
    
    /**
     * Sends a bunch of mails
     */
    public function sendNewsletter($draft, $receivers, $mailoptions)
    {
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile']);
		$textMessage = $this->readNewsletterFile($draft['textfile']);

		$htmlMessage = $this->transformHTML($htmlMessage);

		// TODO: convert HTML to TXT with Lynx
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 'From'    => $draft['from']);

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlMessage, $person);
			$customText = $this->customizeMessage($textMessage, $person);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			$mime =& new Mail_mime();
			if($textMessage !== false)
				$mime->setTXTBody(utf8_decode($customText));
			if($htmlMessage !== false)
				$mime->setHTMLBody(utf8_decode($customHtml));
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
    public function sendActivationMail($person)
    {
    		$mailserver = $this->getDefaultMailServer();
    		$options = $this->getMailserverOptions($mailserver);
    		
    		$draft = array(	"from" => "milo@bitflux.ch",
    						"subject" => "Bitflux Newsletter Activation",
    						"textfile" => "activation.en.xhtml");
    		
    		$this->sendNewsletter($draft, array($person), $options);
    }
    
    /**
     * Is called to indicate all messages are saved in the queue
     */
    public function finalizeSend($mailoptions, $maxAmount = 1000)
    {
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		return $mail_queue->sendMailsInQueue($maxAmount);	
    }
    
    /**
     * Add custom style to the HTML document to replace the missing .css style sheet
     */
    protected function transformHTML($inputMessage)
    {
		return $inputMessage;   	
    }
    
    /**
     * Customized the message for a certain user
     */
    protected function customizeMessage($message, $person)
    {
		$title = $person['gender'] == '0' ? 'Herr' : 'Frau';
		return str_replace(array('{firstname}', '{lastname}', '{email}', '{title}', '{activation}'), 
							array($person['firstname'], $person['lastname'], $person['email'], $title, $person['activated']), $message);
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
}

?>
