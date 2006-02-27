<?php

class bx_editors_ooo extends bx_editor implements bxIeditor {    
    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			$params=array();
			$params['pipelineName'] = 'ooo';
      $params['xslt'] = 'ooo.xsl';
			return $params;
		}
    
    public function getImage($dom,$tmpdir){
        $xp = new DomXPath($dom);
        $xp->registerNamespace("xhtml", "http://www.w3.org/1999/xhtml");
        
        $manifest = new DomDocument();
        $manifest->load(BX_INCLUDE_DIR."bx/editors/ooo/manifest.xml");
        
        $results = $xp -> query("/xhtml:html/xhtml:body//xhtml:img");
        foreach($results as $result){
            $src =  $result->getAttribute("src");
            // stop cheating
            
            $src = str_replace("..","",$src);

            if(strpos($src,BX_WEBROOT) === 0){
               $src = str_replace(BX_WEBROOT,"/",$src);
            }
            if(strpos($src,"http://") === false){
                if (strpos($src,"/files/images/ooo/") === false) {
                    
                    $ext=strrchr($src, ".");

                    $newname = strtoupper(md5($src))."$ext";
                    
                } else {
                    $newname = str_replace("/files/images/ooo/","",$src);
                }
                
                copy(BX_OPEN_BASEDIR.$src, $tmpdir ."/Pictures/".$newname);
                $result->setAttribute("src","Pictures/".$newname);
                $size = getimagesize($tmpdir."/Pictures/".$newname);
                $sizeh = $size[1] / 38;
                $sizew = $size[0] / 38;
                $result->setAttribute("height",$sizeh."cm");
                $result->setAttribute("width",$sizew."cm");
                $entry = $manifest->createElementNS("urn:oasis:names:tc:opendocument:xmlns:manifest:1.0","manifest:file-entry");
                $entry->setAttributeNS("urn:oasis:names:tc:opendocument:xmlns:manifest:1.0","full-path","Pictures/".$newname);
                $entry->setAttributeNS("urn:oasis:names:tc:opendocument:xmlns:manifest:1.0","media-type",$size['mime']);
                
                $manifest->documentElement->appendChild($entry);

            }
        }
        
        $manifest->save($tmpdir."/META-INF/manifest.xml");
    }
    
    public function getImageFromZip($dom, $tmpfname){
        $xp = new DomXPath($dom);
        $xp->registerNamespace("office","urn:oasis:names:tc:opendocument:xmlns:office:1.0");
        $xp->registerNamespace("draw", "urn:oasis:names:tc:opendocument:xmlns:drawing:1.0");
        $results = $xp -> query("/office:document-content/office:body/office:text//draw:frame/draw:image");
       
        foreach($results as $result){
            
            
            
            $src =  $result->getAttributeNS("http://www.w3.org/1999/xlink","href");
            $oldname = $src;
            if(strpos($src,"Pictures/") === 0) {
                $src = str_replace("Pictures/","",$src);
                // stop cheating
                $src = str_replace("..","",$src);
                
                if (strpos($src,"_") !== false) {
                    $filename = str_replace("_","/",$src);    
                } else {
                    $filename = "files/images/ooo/".$src;
                }
                
                $dir = dirname(BX_OPEN_BASEDIR.$filename);
                if (!file_exists($dir)) {
                    $test=mkdir($dir,0755,true);
                }
                
                copy($tmpfname."/".$oldname, BX_OPEN_BASEDIR.$filename);
                
                $result->setAttribute("src","/".$filename);
            }
        }
    }
    
    public function uploadFile($path, $id, $data) {
        if(empty($data["uploadfile"]['tmp_name'])){
            print "<script type='text/javascript'>";
            print "alert('No Upload File!!!')";
            print "</script>";
        }
        else
        {
            $dom = new domdocument();
            include_once("Archive/Zip.php");
            $ar = new Archive_Zip($data["uploadfile"]["tmp_name"]);
            
            $tmpfname=BX_TEMP_DIR."/ODT".md5(time() . rand());
            mkdir($tmpfname);
            $ar->extract(array("add_path" => $tmpfname));
                                
            $dom->load($tmpfname."/content.xml");
            
            $cp = $this->getImageFromZip($dom, $tmpfname);
            
            $xsl = new domdocument();
            $xsl->load(BX_INCLUDE_DIR."bx/editors/ooo/odt2html.xsl");
            
            $proc = new xsltprocessor();
            
            $proc->importStylesheet($xsl);
            
            $proc->transformToUri($dom,BX_DATA_DIR.$id);
            bx_helpers_file::rmdir($tmpfname);
        }
    }
    
    public function getEditContentById($id) {
        if (isset($_GET['download']) && $_GET['download'] == 'true') {
            $dom = new domdocument();
            $dom->load(BX_DATA_DIR."$id");
            
            
            $dir = BX_LIBS_DIR."editors/ooo/skeleton";
            $todir = BX_TEMP_DIR."/SKELETON".md5(time() . rand());
            bx_helpers_file::cpdir($dir,$todir);
       
            
            $cp = $this->getImage($dom,$todir);
            
            $xsl = new domdocument();
            $xsl->load(BX_INCLUDE_DIR."bx/editors/ooo/html2odt.xsl");
            
            $proc = new xsltprocessor();
            
            //header("Content-type: text/xml");
            $curdir = getcwd();
                         
            $proc->importStylesheet($xsl);
            
            $proc->transformToUri($dom,$todir."/content.xml");
            
            $filename=BX_TEMP_DIR."/ODT".md5(time() . rand()).".odt";
            chdir($todir);
            exec("zip -r ".$filename." * ");
            chdir($curdir);
            //bx_helpers_file::rmdir($todir);
            header("Content-Type: application/vnd.oasis.opendocument.text");
            $downloadfile = str_replace("/","-",substr($id,1));
            header("Content-Disposition: attachment; filename=".str_replace(".xhtml",".odt",$downloadfile));
            readfile($filename);
            unlink($filename);
            
        };
        return null;
    }
    
    public function getDisplayName() {
        return "OpenOffice";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
    }
}

?>
