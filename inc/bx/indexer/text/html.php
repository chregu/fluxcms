<?php


class bx_indexer_text_html {
    
    
    public function getMetadataForFile($file) {
        
        $props['bx:']['fulltext'] = strip_tags(file_get_contents($file));
        return $props;
        
    }
    
}