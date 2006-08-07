<?php
class bx_plugins_admin_themes extends bx_plugins_admin implements bxIplugin  {
    static private $instance = null;
    public static function getInstance($mode) {
        if (!self::$instance) {
            self::$instance = new bx_plugins_admin_themes($mode);
        } 
        
        return self::$instance;
    }
    

   /* protected function getFullPath($path, $name, $ext) {
        return $path.$name;
    }*/
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        if ($ext) {
            return $name.".$ext";    
        } else if ($name == '') {
            return '/';
        } else {
            return $name;
        }
        
    }
    
    public function getContentById($path, $id) {
    	
		$perm = bx_permm::getInstance();
		if (!$perm->isAllowed('/permissions/',array('permissions-back-themes'))) {
    		throw new BxPageNotAllowedException();
    	}
    	
        $url = bx_helpers_config::getOption('themesDownloadUrl',true);
        
        $sc = popoon_helpers_simplecache::getInstance();
        $xml = $sc->simpleCacheHttpRead($url,3600);
        
        $dom = new domdocument();
        $dom->loadXML($xml);
        
        if(isset($_GET['downloadlink'])) {
            $xp = new domxpath($dom);
            $results = array();
            $results = $xp->query('/themes/theme[downloadLink/text() = "'.$_GET['downloadlink'].'"]');
            if ( $results->length > 0) {
                $this->downloadThemeZip($_GET['downloadlink']);
                @$node = $results->item(0);
                $theme = $xp->query("themeFolder",$node)->item(0)->nodeValue;
                @$themeCss = $xp->query("themeCss",$node)->item(0)->nodeValue;
                @$themePic = $xp->query("picLink",$node)->item(0)->nodeValue;
                $this->getPreviewPicture($themePic, $theme, $themeCss);
                
                if ($theme) {
                    bx_config::setConfProperty("theme",$theme);
                }
                if ($themeCss) {
                    bx_config::setConfProperty("themeCss",$themeCss);
                }
                $xml ="<installed>Installation succeeded! </installed>";
                
            } else {
                $xml ='<notinstalled>"'.$_GET['downloadlink'].'" is not a valid Link</notinstalled>';
            }
                
             
            return domdocument::loadXML($xml);
            
        } else  {
        
        return $dom;
        }
    }
    
    
    /* FIXME:: this should be cleaned up. arguments are $path,$id,$data,$mode */
    public function handlePost($path, $name, $ext, $data=null) {
        
     
        
        
    }
    
    
    public function resourceExists($path, $name, $ext) {
        return TRUE;
    }
    
    public function getDataUri($path, $name, $ext) {
        return FALSE;
    }

    public function getEditorsByRequest($path, $name, $ext) {
        return array();   
    }

    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return TRUE;
    }
    
    public function downloadThemeZip($fileLink){
        preg_match("#.*/#", $fileLink, $link);
        $zipFile = explode($link[0], $fileLink);
        $tempname = tempnam(BX_TEMP_DIR,"theme");
        $sc = popoon_helpers_simplecache::getInstance();
        file_put_contents($tempname, $sc->simpleCacheHttpRead($fileLink,3600));
        $check = $this->unzip($tempname);
        @unlink($tempname);
     }
    
    public function unZip($zipFile) {
        exec("unzip -o ". escapeshellarg($zipFile) ." -d ".BX_OPEN_BASEDIR."themes");
    }
    
    public function getPreviewPicture($themePic, $theme, $themeCss) {
        $themePicName = preg_replace("#.css#", ".jpg", $themeCss);
        $sc = popoon_helpers_simplecache::getInstance();
        @mkdir (BX_OPEN_BASEDIR."themes/".$theme."/preview/",0755,true);
        file_put_contents(BX_OPEN_BASEDIR."themes/".$theme."/preview/".$themePicName, $sc->simpleCacheHttpRead($themePic,3600));
    }
    
}
?>
