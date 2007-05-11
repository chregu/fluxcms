<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+


class bx_filters_xslt extends bx_filter {
    
    static private $instance = NULL;

    public $lang = 'de';
    public $defaultLang = 'de';
    
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_filters_xslt($mode);
        } 
        return self::$instance;
    }   
    
    public function preHTML(&$xml,$path = NULL, $filename = NULL) {
        
        
        $xslSrc = $this->getParameter($path,"xslt");
        $xsl = new XsltProcessor();
        $xslDom = new DomDocument();
        $xslDom->load($xslSrc);
        $xsl->setParameter("","lang",$GLOBALS['POOL']->config->getOutputLanguage());
        $xsl->importStylesheet($xslDom);
        $this->getParameterAll($path);
        $xml =  $xsl->transformToDoc($xml);
        
    }

    

}


?>
