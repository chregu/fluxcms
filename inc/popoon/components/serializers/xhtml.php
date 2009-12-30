<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

include_once("popoon/components/serializer.php");

/**
* Documentation is missing at the moment...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_serializers_xhtml extends popoon_components_serializer {
    
    public $XmlFormat = "Own";
    public $contentType = "text/html; charset=utf-8";
    
    function __construct (&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        // if internal request, don't do the usual transformation, just "print" it
        if ($this->sitemap->options->internalRequest) {
            if (is_object($xml)) {
                $this->sitemap->hasFinalDom = true;
            } else {
                print $xml;
            }
            return true;
        }
        $encoding = $this->getParameterDefault("contentEncoding");
        
        if (!is_object($xml) && $this->getParameter('default','obfuscateMailJS') == 'true') {
                $xml = domdocument::loadXML($xml);
        }
        
        if (is_object($xml)) {
            if ($encoding) {
                $xml->encoding = $encoding;
            }
            if ($this->getParameter('default','obfuscateMailJS') == 'true') {
                $xp = new domxpath($xml);


                $xp->registerNamespace("xhtml","http://www.w3.org/1999/xhtml");
                $res = $xp->query("/xhtml:html/xhtml:body//xhtml:a[starts-with(@href,'mailto:')]");
                if ($res->length > 0) {
                    $z = 0;
                    foreach ($res as $node) {
                        $z++;
                        if ($node->parentNode instanceof DOMElement) {
                            $str = $xml->saveXML($node);
                            $str = $this->utf8_strrev(str_replace(array("'","@","mailto:","<"),array("\'",'___',"schickzu:",'_lt_'),$str));
                            $scr = '<script type="text/javascript">';
                            
                            $scr .= '//<![CDATA[
                            ';                       
                            if ($z == 1) {
                                $scr .= 'function obfscml(t) { var s = ""; var i = t.length; while (i>0) { s += t.substring(i-1,i); i--; } document.write(s.replace(/_lt_/g,unescape("%3C")).replace(/schickzu:/g,"mailto:").replace(/___/g,unescape("%40"))); }';
                            }
                            $scr .= "obfscml('".$str."')";
                            $scr .= '//]]></script>';
                            $fr = bx_helpers_xml::getFragment($scr,$xml);
                            
                            $node->parentNode->replaceChild($fr,$node);
                        }
                        
                    }
                }
            }
                
                
            $this->sitemap->hasFinalDom = true;
            $xmlstr = $xml->saveXML();
        } else {
            $xmlstr = $xml;
            unset ($xml);
        }
        if ($errhandler = $this->getParameterDefault("outputErrors")) {
            $err = $this->getErrorReporting($errhandler);
            if ($err) {
                $xmlstr = str_replace("</html>",$err."</html>",$xmlstr);
            }
        }
        print $this->cleanXHTML($xmlstr);
        
    }

	protected function utf8_strrev($str){
   		preg_match_all('/./us', $str, $ar);
   		return join('',array_reverse($ar[0]));
	}
    
    private function cleanXHTML($xml) {
        /* for some strange reasons, libxml makes an upercase HTML, which the w3c validator doesn't like */
        if ($this->getParameterDefault("stripScriptCDATA") == "true") {
            $xml = $this->stripScriptCDATA($xml);
        }
        
        /*if ($this->getParameterDefault("stripDefaultPrefixes") == "true") {
            $xml = $this->stripDefaultPrefixes($xml);
        }*/
        
        if ($this->getParameterDefault("stripBxAttributes") == "true") {
            $xml = $this->stripBxAttributes($xml);   
        }
        if ($this->getParameterDefault("stripXMLDeclaration") == "true") {
            $xml = str_replace("&#13;","",$xml);
            $xml = preg_replace("#<\?xml[^>]*\?>\s*#","",$xml);
        }
//        return $this->obfuscateMail(str_replace(array('<default:','</default:','xmlns:i18n="http://apache.org/cocoon/i18n/2.1"',"DOCTYPE HTML"),array('<','</',"","DOCTYPE html"),$xml));
        return $this->obfuscateMail(str_replace(array('<default:','</default:',"DOCTYPE HTML"),array('<','</',"DOCTYPE html"),$xml));
    }
    
    /*private function stripDefaultPrefixes($xml) {
        return str_replace(array('<default:','</default:'),array('<','</'),$xml);
    }*/
    private function stripScriptCDATA($xml) {
        //strip empty (Whitespace only) CDATA
        $xml = preg_replace("#<!\[CDATA\[\W*\]\]>#","",$xml);
        // strip CDATA just after <script>
        $xml = preg_replace("#(<script[^>]*>)\W*<!\[CDATA\[#","$1",$xml);
        // strip ]]> just before </script>
        return preg_replace("#\]\]>\W*(</script>)#","$1",$xml);
    }
    
    private function stripBxAttributes($xml) {
        $xml = str_replace('xmlns:i18n="http://apache.org/cocoon/i18n/2.1"','',$xml);
        return preg_replace("#\sbx[a-zA-Z_]+=\"[^\"]+\"#","",$xml);  
    }
    
    private function obfuscateMail($xml) {
        if ($this->getParameter('default','obfuscateMail') == 'true') {
            return preg_replace('#mailto:([^@]*)@([^"]+)#','&#109;&#97;&#105;&#108;&#116;&#111;&#58;$1&#64;$2',$xml);
        }
        return $xml;
    }
    
    private function getErrorReporting($class) {
        $err = call_user_func(array($class,'getInstance'));
        if ($err->hasErrors()) {
            return $err->getHtml();
        } else {
            return null;
        }
        restore_error_handler();
    }
	
    
    
    
}


?>
