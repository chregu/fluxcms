<?php


class bx_indexer_text_html {
    
    
    public function getMetadataForFile($file) {
        $dom = new domdocument();
        $dom->load($file);
        $xp = new domxpath($dom);
        $xp->registerNamespace("xhtml","http://www.w3.org/1999/xhtml");

        $res = $xp->query("/xhtml:html/xhtml:body");
        $node = $res->item(0);
        $props['bx:']['fulltext'] = bx_helpers_string::utf2entities(strip_tags($dom->saveXML($node)));
        return $props;
        
    }
    
}