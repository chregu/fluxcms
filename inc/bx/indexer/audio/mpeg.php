<?php


class bx_indexer_audio_mpeg {
    
    
    public function getMetadataForFile($file) {
        
        $id3 = &new MP3_Id();
        $id3->read($file);
        //$id3->study();
        $props = array();
        $props['id3'] = array();
        $props['http://purl.org/dc/elements/1.1/'] = array();
        $props['http://purl.org/dc/elements/1.1/']['creator'] = $id3->getTag('artists',NULL);
        $props['http://purl.org/dc/elements/1.1/']['title'] = $id3->getTag('name',NULL);
        //$props['http://purl.org/dc/elements/1.1/']['date'] = $id3->getTag('year');
        $props['http://purl.org/dc/elements/1.1/']['subject'] = $id3->getTag('genre',NULL);
        
        
        //$props['kMDItem']['AudioBitRate'] = $id3->getTag('bitrate');
        $props['id3']['Album']  = $id3->getTag('album',NULL);
        $props['id3']['Name']   = $id3->getTag('name',NULL);
        $props['id3']['Artists']= $id3->getTag('artists',NULL);
        $props['id3']['Year']   = $id3->getTag('year',NULL);
        $props['id3']['Comment']= $id3->getTag('comment',NULL);
        $props['id3']['Track']  = $id3->getTag('track',NULL);
        $props['id3']['Genre']  = $id3->getTag('genre',NULL);
        
        //$props['kM']['DurationSeconds'] = $id3->getTag('lengths');
        $props['bx:']['mimetype'] = 'audio/mpeg';
        $props['bx:']['filesize'] = filesize($file);
        return $props;
    }
    
}
