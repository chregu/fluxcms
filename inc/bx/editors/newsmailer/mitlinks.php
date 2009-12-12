<?php

/**
 * MITLinks Newsletter
 */
class bx_editors_newsmailer_mitlinks extends bx_editors_newsmailer_newsmailer {    
   
   protected static $textHeader;
   
    /**
     * Sends a bunch of mails
     */
    public function autoPrepareNewsletter($draftId)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);	

    	// mysqli(mysqli)://fluxcms:fluxcms@localhost/fluxcms
    	eregi("^([^//]*)//([^:]*):([^@]*)@([^/]*)/(.*)", $GLOBALS['POOL']->dbwrite->getDSN(), $dbparams);
    	
    	self::$textHeader = 
'dbhost: '.$dbparams[4].'
dbuser: '.$dbparams[2].'
dbpass: '.$dbparams[3].'
dbtable: '.$prefix.'mail_queue
dbname:  '.$dbparams[5].'
listname: testliste
password: daspasswort!

STARTMESSAGE
To: <TMPL_VAR NAME="firstname"> <TMPL_VAR NAME="lastname"> <<TMPL_VAR
NAME="email">>
From: '.$draft['from'].'
Subject: '.$draft['subject'].'

';

    	parent::autoPrepareNewsletter($draftId);
    }
   
    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name, $type)
    {
    	$content = parent::readNewsletterFile($name, $type);
    	
    	if($type == "text") {
	    	// Add the MITLinks specific header for their spambot
	    	$content = self::$textHeader.$content;
    	}
    	
		return $content;
    }
}

?>
