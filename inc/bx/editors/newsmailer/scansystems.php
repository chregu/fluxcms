<?php

/**
 * Scan Systems Newsletter
 */
class bx_editors_newsmailer_scansystems extends bx_editors_newsmailer_newsmailer {    
   
    /**
     * Add custom style to the HTML document to replace the missing .css style sheet
     */
    protected function transformHTML($inputMessage)
    {
		$xsl = new DomDocument();
		$xsl->load('themes/3-cols/scansystems.xsl');
		$inputdom = new DomDocument();
		$inputdom->loadXML($inputMessage);
		$proc = new XsltProcessor();
		$xsl = $proc->importStylesheet($xsl);
		$newdom = $proc->transformToDoc($inputdom);
		return $newdom->saveXML();    	
    }
}

?>
