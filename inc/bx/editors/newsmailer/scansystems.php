<?php

/**
 * Scan Systems Newsletter
 */
class bx_editors_newsmailer_scansystems extends bx_editors_newsmailer_newsmailer {    
   
    /**
     * Add custom style to the HTML document to replace the missing .css style sheet
     */
    protected function transformHTML($inputdom)
    {
		$xsl = new DomDocument();
		$xsl->load('themes/'.bx_helpers_config::getTheme().'/scansystems.xsl');
		$proc = new XsltProcessor();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
}

?>
