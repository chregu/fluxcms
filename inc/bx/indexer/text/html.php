<?php


class bx_indexer_text_html {
    
    
    public function getMetadataForFile($file) {
        $dom = new domdocument();
        $dom->loadHTMLFile($file);
        $xp = new domxpath($dom);
        $res = $xp->query("/html/body");
        $node = $res->item(0);
        $props['bx:']['fulltext'] = strip_tags($dom->saveXML($node));
        return $props;
        
    }
    
}