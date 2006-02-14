<?php

class bx_helpers_xml {
    
    
    static function innerXML($node, $fragment) {
        //delete child nodes
        foreach ($node->childNodes as $child) {
            $node->removeChild($child);
        }
        //make docfrag document
        $frag = new DomDocument();
        $frag->loadXML("<root>".$fragment."</root>");
        
        $doc = $node->ownerDocument;
        foreach($frag->documentElement->childNodes as $child) {
            $node->appendChild($doc->importNode($child,true));
        }
        
        unset ($frag);
        return $node;
    }
    
    static function copyChildNodes($sourceNode, $targetNode) {
        if($sourceNode->childNodes->length > 0) {
            foreach($sourceNode->childNodes as $node) {
                $inode = $targetNode->ownerDocument->importNode($node, TRUE);
                $targetNode->appendChild($inode);
            }
            return TRUE;
        }
        return FALSE;
    }
    
    static function array2Dom($arr, &$dom, &$parent) {
        if (is_array($arr)) {
            $domNode=null;
            foreach($arr as $key => $value) {
                if (strpos($key, "@") === 0 && !is_array($value)) {
                    $parent->setAttribute(substr($key,1), $value);
                } else {
                    $key = preg_match("#^[0-9]+$#", $key) ? "entry": $key;
                    $domNode = $dom->createElement($key);
                    if (is_array($value)) {
                        bx_helpers_xml::array2Dom($value, $dom, $domNode);
                    } else {
                        $domNode->nodeValue = $value;
                    }
                    $parent->appendChild($domNode);
                }
            }
            return $domNode;
        }
    }   
    
}
