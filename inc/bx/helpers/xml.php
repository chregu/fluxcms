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
    
    
}