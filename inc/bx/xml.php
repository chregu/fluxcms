<?php

class bx_xml {

    static function xsltFromFile($filename) {
            $xsl = new XsltProcessor();
            $xsl->importStylesheet(bx_xml::domFromFile($filename));
            return $xsl;
    }
    
    static function domFromFile($filename) {
            $xmldom = new DomDocument();
            $xmldom->load($filename);
            return $xmldom;
    }
        
            

}
