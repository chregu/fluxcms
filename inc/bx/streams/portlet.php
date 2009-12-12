<?php

class bx_streams_portlet extends bx_streams_buffer {
    
    
    function contentOnRead($path) {
        $parts =  bx_collections::getCollectionAndFileParts($path, "output");
        try {
            $xml = $parts['coll']->getContentByRequest($parts["name"],$parts["ext"]);
        } catch (Exception $e) {
            $xmlstr = '<?xml version="1.0" encoding="UTF-8"?>
            
            <error:notify
            error:type="error" 
            error:sender="org.apache.cocoon.sitemap.ErrorNotifier"
            xmlns:error="http://apache.org/cocoon/error/2.0">
            
            <error:title>'.get_class($e).'</error:title>
            
            <error:message>'.$e->getMessage().'</error:message>
            <error:code>'.$e->getCode().'</error:code>
            
            <error:file>'.$e->getFile().'</error:file>
            <error:line>'.$e->getLine().'</error:line>';
            
            if (isset ($e->userInfo)) {
                $xmlstr  .= '<error:extra description="userInfo">'.$e->userInfo.'</error:extra>
                </error:notify>';
                return $xmlstr;
            }
            $xmlstr .= '<error:extra description="stacktrace">'.$e->getTraceAsString().'</error:extra>
            </error:notify>';
            
            return $xmlstr;
        }
        return $xml->saveXML();
    }
    
    function contentOnWrite($content) {
    }
    
    function getAttrib($name) {
        
        return $this->getParameter($name);
    }
    
    
}

