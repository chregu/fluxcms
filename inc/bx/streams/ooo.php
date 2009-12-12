<?php


class bx_streams_ooo extends bx_streams_buffer {
    
    function contentOnRead($path) {
        $la = exec (escapeshellcmd("unzip -o " . escapeshellarg($path). " content.xml -d " . BX_PROJECT_DIR. "/data"));
       if ($la) {
            //unlink($this->tmpname);
            
            $xsl = bx_xml::xsltFromFile(dirname(__FILE__)."/ooo/ooo2html.xsl");
            
            $xml = bx_xml::domFromFile( BX_PROJECT_DIR. "/data/content.xml");
            $xml = $xsl->transformToDoc($xml);
           return $xml->saveXML();
           
        }
        return "error";
    }
    
    function contentOnWrite($content) {
        //
    }
}



?>
