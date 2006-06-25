<?php
class bx_helpers_string {
    
    static function truncate($inStr, $length = 100, $breakWords = false, $etc = '...') {
        if ($length == 0)
        return '';
        
        if (strlen($inStr) > $length) {
            $length -= strlen($etc);
            if (!$breakWords) {
                $inStr = preg_replace('/\s+?(\S+)?$/', '', substr($inStr, 0, $length + 1));
            }
            
            return substr($inStr, 0, $length)." $etc";
        } else
        return $inStr;
    }
    
    static function nl2property_hegu($text) {
        $text = $text[0]->ownerDocument->saveXML($text[0]);
        $text = preg_replace(array('#<[\/]*meta_other>#', "/^([^:\n]*):(.*)$/m"), array('', "<name>\$1</name><value>\$2</value>"), $text);
        $text = "<div><p>".preg_replace(array("#\n#"),array("</p>\n<p>", ''),$text)."</p>\n</div>";
        $xml = new DomDocument();
        $xml->loadXML($text);
        return $xml;
    }
    
    static function explodeToNode($separator, $string, $childNodeName='child', $rootNodeName='root') {
        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement($rootNodeName));
        
        if(!empty($string)) {
            $exploded = explode($separator, $string);
            foreach($exploded as $element) {
                $child = $dom->createElement($childNodeName);
                $tn = $dom->createTextNode($element);
                $child->appendChild($tn);
                $dom->documentElement->appendChild($child);
            }
        }
        return $dom;
    }
    
    /**
    * takes a string of utf-8 encoded characters and converts it to a string of unicode entities
    * each unicode entitiy has the form &#nnnnn; n={0..9} and can be displayed by utf-8 supporting
    * browsers
    *
    * from http://ch.php.net/manual/en/function.utf8-decode.php and optimized
    *
    * @param $source string encoded using utf-8 [STRING]
    * @return string of unicode entities [STRING]
    * @access public
    */
    static function utf2entities($source,$force = false) {
        if (!$force && $GLOBALS['POOL']->config->dbIsUtf8) {
            return $source;
        }
        // array used to figure what number to decrement from character order value
        // according to number of characters used to map unicode to ascii by utf-8
        $decrement[4] = 240;
        $decrement[3] = 224;
        $decrement[2] = 192;
        $decrement[1] = 0;
        
        // the number of bits to shift each charNum by
        $shift[1][0] = 0;
        $shift[2][0] = 6;
        $shift[2][1] = 0;
        $shift[3][0] = 12;
        $shift[3][1] = 6;
        $shift[3][2] = 0;
        $shift[4][0] = 18;
        $shift[4][1] = 12;
        $shift[4][2] = 6;
        $shift[4][3] = 0;
        
        $pos = 0;
        $len = strlen ($source);
        $encodedString = '';
        while ($pos < $len) {
            $thisLetter = substr ($source, $pos, 1);
            $asciiPos = ord ($thisLetter);
            $asciiRep = $asciiPos >> 4;
            
            if ($asciiPos < 128) {
                $pos += 1;
                $thisLen = 1;
            }
            else if ($asciiRep == 12 or $asciiRep == 13) {
                // 2 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 2);
                $pos += 2;
                $thisLen = 2;
            }
            else if ($asciiRep == 15) {
                // 4 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 4);
                $thisLen = 4;
                $pos += 4;
            }
            else if ($asciiRep == 14) {
                // 3 chars representing one unicode character
                $thisLetter = substr ($source, $pos, 3);
                $thisLen = 3;
                $pos += 3;
            }
            
            // process the string representing the letter to a unicode entity
            
            if ($thisLen == 1) {
                $encodedLetter =$thisLetter;
            } else {
                $thisPos = 0;
                $decimalCode = 0;
                while ($thisPos < $thisLen) {
                    $thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
                    if ($thisPos == 0) {
                        $charNum = intval ($thisCharOrd - $decrement[$thisLen]);
                        $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                    }
                    else {
                        $charNum = intval ($thisCharOrd - 128);
                        $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                    }
                    
                    $thisPos++;
                }
                if ($decimalCode < 65529) {
                    $encodedLetter = "&#". $decimalCode. ';';
                } else {
                    $encodedLetter = "";
                }
            }
            $encodedString .= $encodedLetter;
            
        }
        return $encodedString;
    }

    
    static function array2query ($params) {
        $str = '';
        if(!empty($params)) {
            foreach ($params as $key => $value) {
                $str .= (strlen($str) < 1) ? '' : '&';
                $str .= $key . '=' . rawurlencode($value);
            }
        }
        return ($str);
    }
    
    static function makeUri ($title, $preserveDots = false) {
        $title = html_entity_decode($title,ENT_QUOTES,'UTF-8');
        
        $title = trim($title);
        if (!$title) {
            $title = "none";   
        }
        $newValue= $title;
        if (!$preserveDots) {
            $newValue= str_replace(".","-",$newValue);
        }
        $newValue = str_replace("@","-at-",$newValue);
        $newValue= preg_replace("/[öÖ]/u","oe",$newValue);
        $newValue= preg_replace("/[üÜ]/u","ue",$newValue);
        $newValue= preg_replace("/[äÄ]/u","ae",$newValue);
        $newValue= preg_replace("/[éèê]/u","e",$newValue);
        $newValue= preg_replace("/[Ïïíì]/u","i",$newValue);
        $newValue= preg_replace("/[ñ]/u","n",$newValue);
        $newValue= preg_replace("/[àåáâ]/u","a",$newValue);
        $newValue= preg_replace("/[ùú]/u","u",$newValue);
        $newValue= preg_replace("/[òó]/u","o",$newValue);
        $newValue= preg_replace("/[ß]/u","ss",$newValue);
        
        
        $newValue= preg_replace("/[\n\r]*/u","",$newValue);
        //removing everything else
        $newValue = strtolower($newValue);
        $newValue = preg_replace("/[^a-z0-9\.\-\_]/","-",$newValue);
        
        
        if (!$preserveDots) {
            $newValue= preg_replace("/_([0-9]+)$/u","-$1",$newValue);
        } else {
            $newValue= preg_replace("/_([0-9]+)\./u","-$1.",$newValue);
        }

        $newValue= preg_replace("/-{2,}/u","-",$newValue);

        $newValue = trim($newValue,"-");
        if (!$newValue) {
            $newValue = "none";
        }
        return $newValue;
    }
    
    /* urify is a simple version of the above.
       The reason for having 2 versions is, that the above is not easy to do in XSLT only and
       I don't want to change makeUri 'cause of BC.
       
       in XSLT, the function is the following:
       
        <func:function name="bxf:urify">
            <xsl:param name="text"/>
            <func:result select="translate($text,'ABCDEFGHIJKLMNOPQRSTUVWXYZ&#x20;&#x9;&#xA;&#xD;ÄäÜüÖöÏïçÊèÉéÊêÀàÂâÔô_;:\.!,?+$£*ç%&amp;/()=','abcdefghijklmnopqrstuvwxyz----aauuooiiceeeeeeaaaaoo')"/>
        </func:function>
    */
    static function urify($text) {
        $newValue = strtolower(preg_replace("/[_;:\.!,?+$£*ç%&\/\(\)=]/","",$text));
        $newValue= preg_replace("/[öÖÔô]/u","o",$newValue);
        $newValue= preg_replace("/[üÜ]/u","u",$newValue);
        $newValue= preg_replace("/[äÄàÀâ]/u","a",$newValue);
        $newValue= preg_replace("/[ÊèÉéÊ]/u","e",$newValue);
        $newValue= preg_replace("/[Ïï]/u","i",$newValue);
        $newValue= preg_replace("/[ç]/u","c",$newValue);
        return str_replace(" ","-",$newValue);        
    }        

    /**
    * replaces all occurrences of the keys of $textfields in $subject.
    *
    * @param string $subject string containing fieldnames sourrounded by {} which should be replaced
    * @param array $textfields array of key=>value containing the field values
    * @return string string with replaced fields
    * @access public
    */
    function replaceTextFields($subject, $textfields) {
        foreach($textfields as $field => $value) {
            $patterns[] = '/\{'.$field.'\}/';
            $replacements[] = $value;
        }
        $subject = preg_replace($patterns, $replacements, $subject);
        return $subject;
    }
    
    /**
    * tidily prints the given fields into a string
    *
    * @param array $fields array of key=>value containing the field values
    * @param boolean $printKey when set to TRUE, the key gets printed as well
    * @return string string with formatted fields
    * @access public
    */
    static function formatTextFields($fields, $printKey = TRUE, $hideFields = array()) {
        $out = '';
        foreach($fields as $key => $value) {
            if($printKey) {
                $out .= sprintf('%-20s: ', $key);
            }
            if (strpos($value,"\n") !== false) {
                $value = "\n\n  ".preg_replace("#([\r\n]+)#","$1  ",$value). "\n****";
            }
            $out .= "$value\n";
        }
        return $out;
    }
    
    /**
    * strips all newlines (\r and \n) from the given string (utf8 save),
    * shortens repeating whitespaces to one character and strips ws from 
    * the beginning and the end.
    *
    * @param string $in string to trim
    * @return string trimmed string
    * @access public
    */
    static function trim($in) {
        $in = trim($in);
        $in = preg_replace('/[\s]{2,}/u', ' ', $in);
        $in = preg_replace('/[\r\n]*/u', '', $in);
        return $in;
    }
    
    static function tidyfy ($string) {
        $tidyOptions = array(
        "output-xhtml" => true,
        "show-body-only" => true,
        "clean" => false,
        "wrap" => "0",
        "indent" => false,
        "indent-spaces" => 1,
        "ascii-chars" => false,
        "wrap-attributes" => false,
        "alt-text" => "",
        "doctype" => "loose",
        "numeric-entities" => true,
        "drop-proprietary-attributes" => true
        );
        
        if (class_exists("tidy")) {
            $tidy = new tidy();
            if(!$tidy) {
                return $string;
            }
        } else {
            return $string;
        }
        
        // this preg escapes all not allowed tags...
        $tidy->parseString($string,$tidyOptions,"utf8");
        $tidy->cleanRepair();
        return (string) $tidy;
    }
    
    static function makeLinksClickable($text) {
        //$res=preg_replace("/((http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/i", "<a href=\"\\0\">\\0</a>", $text);
        $res = preg_replace( "#([\s\(\.\:]|\A)(http[s]?:\/\/[^\s^>^<^\)]*)#m", "$1<a href=\"$2\">$2</a>", $text);
        return $res;
    }
    
     static function removeDoubleSlashes($str) {
        return preg_replace("#\/{2,}#","/",$str);
    }
    
}

?>
