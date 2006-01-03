<?php


require_once 'HTTP/Request.php';
require_once 'XML/Unserializer.php';

class Services_flickr {
    
    public $apiUrl = "http://www.flickr.com/services/rest/";
    public $apiKey = "57bded31ef9c635326e4acfa2c62b7dc";
    private $_us = null;
    
    function __construct($userid) {
        
        $this->userId = $userid;   
    }
    
    public function getPhotoSets () {
        
        $sets =  $this->_setRequest("flickr.photosets.getList");
        return $sets['photosets']['photoset'];
        
    }
    
    public function getPhotos($photosetid) {
        $photos = $this->_setRequest("flickr.photosets.getPhotos",array("photoset_id" => $photosetid));
        $ret = array();
        foreach ($photos['photoset']['photo'] as $photo) {
            $ret[$photo['id']] = $photo;
        }

        return $ret;
        
    }
    
    public function getPhotoLink ($id, $secret, $size = "") {
        $url = 'http://photos2.flickr.com/'.$id.'_'.$secret;
        if ($size) {
            $url .= '_'.$size;
        }
        $url .= '.jpg';
        return $url;
    }
    public function getPhotoInfo($photoid) {
        $photo = $this->_setRequest("flickr.photos.getInfo",array("photo_id" => $photoid));
        return $photo['photo'];
        
    }
    
    public function getPhotoSizes($photoid) {
        $sizes = $this->_setRequest("flickr.photos.getSizes",array("photo_id" => $photoid));
        $ret = array();
        foreach ($sizes['sizes']['size'] as $size) {
            $ret[$size['label']] = $size;
        }
        return $ret;
    }
        
    
    private function _setRequest(  $method, $params = array()) {
        $url = sprintf('%s?method=%s', $this->apiUrl, $method);
        $url .= "&api_key=".$this->apiKey;
        $url .= "&user_id=".$this->userId;
        
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            $url = $url . '&' . $key . '=' . urlencode($value);
        }
        $simplecache = new popoon_helpers_simplecache();
        //$simplecache->cacheDir = "/tmp/";
        $xml = $simplecache->simpleCacheHttpRead($url,3600);
       
        $this->_us = new XML_Unserializer();
        $this->_us->setOption('parseAttributes', true);
        $this->_us->setOption('forceEnum',array("photo"));
        $result = $this->_us->unserialize($xml);
        return $this->_us->getUnserializedData();
    }
}
