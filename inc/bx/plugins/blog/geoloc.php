<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+

class bx_plugins_blog_geoloc {
    
     
     static public function onUpdatePost($post) {
         return self::onInsertNewPost($post);
     }
     static public function onInsertNewPost($post) {
        
       
        $long = null;
        $lat= null;
        $loc = null;
        foreach($post->tags as $key => $tag) {
            $name = null;
            if (strpos($tag,"=")) {
                list($name,$value) = split("=",$tag);   
            } else if (strpos($tag,":") !== false) {
                list($name,$value) = split(":",$tag);
            }
            if ($name) {
                switch ($name) {
                    case "geo:long":
                    case "geo:lon":
                    case "lo":
                    case "long":
                        $long = $value;
                        unset($post->tags[$key]);
                        break;
                    case "geo:lat":
                    case "la":
                    case "lat":
                        $lat = $value;
                        unset($post->tags[$key]);
                        break;
                    case "plaze":
                    case "loc":
                    case "geo:loc":
                    case "location":
                        $loc = $value;
                        unset($post->tags[$key]);
                        break;
                }
            }
        }
        
        
        if ($long || $loc) {
            $info = $post->getInfo();
            $xp = new domxpath($info);
            $res = $xp->query("/info/plazes");
            if ($res->length > 0) {
                $res->item(0)->parentNode->removeChild($res->item(0));   
            }
            $xml = "<plazes fromPlazes='false'>\n";
            $xml .= " <plazelat>".$lat."</plazelat>\n";
            $xml .= " <plazelon>".$long."</plazelon>\n";
            $xml .= " <plazename>".$loc."</plazename>\n";
            $xml .= "</plazes>";
            $post->appendInfoString($xml);
        }
        return $post;
    }
 

}
?>
