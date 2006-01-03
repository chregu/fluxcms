<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+

class bx_plugins_blog_deliciousrdf {
    
    static function getContentById($path,$id,$params) {
            $simplecache = popoon_helpers_simplecache::getInstance();
  
            $simplecache->cacheDir = BX_TEMP_DIR;
            $uri = 'http://del.icio.us:80/rss/'.$params[0];
            
            $t = $simplecache->simpleCacheHttpRead($uri,3600);
            // some installations (my mac..) have problems with installing iconv correctly
            // check that here
            if (function_exists("iconv")) {
                $t = iconv("UTF-8","UTF-8//IGNORE",$t);
            }
            return $t;
    
    }
}
?>
