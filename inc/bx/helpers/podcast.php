<?php
class bx_helpers_podcast {
    
    public static function convert2flv($filename) {
        
        preg_match("#(.*)\.#",$filename,$matches);
        
        
        $podcastname = $matches['1'].md5(floor(time()));
        
        
        if(!file_exists(BX_PROJECT_DIR."files/podcast/".$podcastname.".flv")) {
            exec("ffmpeg -i ".BX_PROJECT_DIR."files/".$filename." ".BX_PROJECT_DIR."files/podcast/".$podcastname.".flv -t 10");
            
            exec("flvtool2 ".BX_PROJECT_DIR."files/podcast/".$podcastname.".flv -U ".BX_PROJECT_DIR."files/podcast/".$podcastname.".flv");
        }
        
        return BX_PROJECT_DIR."files/podcast/".$podcastname.".flv";
    }

}

