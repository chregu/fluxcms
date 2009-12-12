<?php


class bx_indexer_application_pdf {
    
    
    public function getMetadataForFile($file) {
        
        $exec = escapeshellcmd("pdftotext -q $file -");
        
        
        $content = `$exec`;
        $props = array();
        if ($content) {
            $props['bx:']['fulltext'] = $content;
        }
        return $props;
    }
    
}
