<?php
class bx_plugins_gallery_flickr {
    
    protected $photoID = null;
    
    static function getInstance($dom,$path,$id,$params) {
          if (strpos($id,"flickr") === 0) {
           
                return new bx_plugins_gallery_flickr($dom,$path,$id,$params);
          }
    }
    
    function __construct ($dom,$path,$id,$params) {
            $this->flickrUsername = $params['username'];
          
            $flickrID = preg_match("#flickr([0-9]+)/#",$id,$matches);
            $flickrID = $matches[1];
            $this->albumID = $matches[1];
            $photoID = preg_match("#".$matches[1]."/([0-9]+)\.jpg#",$id,$matches);
            if (isset($matches[1])) {
                $this->photoID = $matches[1];
            }
            $this->f = new Services_flickr($params['userid']);
            $this->dom = $dom;
    }
    
    function getImagesAndAlbums (&$options) {
		$photos = $this->f->getPhotos($this->albumID);
        foreach ($photos as $set) {
            
            
            
            if(($options['mode']=='image') 
                || (($options['numberOfImages'] + 1 > ($options['currentPage'] - 1) * $options['imagesPerPage']) 
                    && ($options['numberOfImages'] + 1<= ($options['currentPage']) * $options['imagesPerPage']))) {
                $url = $this->f->getPhotoLink($set['id'], $set['secret'], "s");
                $node = $this->dom->createElement('image');
                $node->setAttribute('href', $set['id'].".jpg");
                $node->setAttribute('imgsrc', $url);
                $node->setAttribute('id', $set['id']);
                $options['images']->appendChild($node);
                if ($set['id'] == $this->photoID) {
                    $link = $this->f->getPhotoLink($this->photoID, $set['secret']);
                    $this->dom->documentElement->setAttribute('imageHref', $link);
                    $this->dom->documentElement->setAttribute('imageLink', 'http://www.flickr.com/photos/'.$this->flickrUsername.'/'.$this->photoID);
                    $this->dom->documentElement->setAttribute('imageId',  $this->photoID);
                }
            }
            $options['numberOfImages']++;
        }
    }
    
    
    
}