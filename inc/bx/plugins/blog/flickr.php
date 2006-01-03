<?php

class bx_plugins_blog_flickr {
    
    static function getContentById($path,$id,$params) {
            $simplecache = new popoon_helpers_simplecache();
  
            $simplecache->cacheDir = BX_TEMP_DIR;
            $uri = 'http://www.flickr.com/services/feeds/photos_public.gne?id='.$params[0] .'@N01&';
            
            if (isset($params[1])) {
                $uri .= 'tags='.$params[1].'&';
            }
            $t = $simplecache->simpleCacheHttpRead($uri,3600);
            return $t;
    
    }
}
?>
