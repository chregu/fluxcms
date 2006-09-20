<?php

class bx_resources_file extends bx_resource {

    protected $fulluri = "";
    protected $mimetype = "";
    protected $id = "";
    public $mock = false;

    public function __construct($id, $mock = false) {
        $this->mimetype = "text/html";
        if (BX_OS_WIN && preg_match("#^[a-zA-Z]:#",$id)) {
            $this->fulluri = $id;
        } else if (substr($id,0,1) == "/") {
            $this->fulluri = $id;
        } else {
            $this->fulluri = BX_OPEN_BASEDIR.$id;
        }
        $this->props['fileuri'] = $id;
        $this->id = "/".str_replace(BX_OPEN_BASEDIR,"",$this->fulluri);
        $this->uri = "/".$id;
        if ($mock === true) {
            $this->mock = true;
        }

        
    }

    protected function init() {
    }


    public function getMimeType() {
        if (isset($this->props['mimetype'])) {
            return $this->props['mimetype'];
        }
        if (is_dir($this->fulluri)) {
            $this->props['mimetype'] = 'httpd/unix-directory';
        } else {
            $this->props['mimetype'] = popoon_helpers_mimetypes::getFromFileLocation($this->fulluri);
        }
        //bx_helpers_debug::webdump( $this->props['mimetype'] );
        return $this->props['mimetype'];

    }

    public function getOutputMimeType() {
        return $this->getMimeType();

    }
    
    public function getOutputUri() {
        if (isset($this->props['outputUri'])) {
            return $this->props['outputUri'];
        } 
        return $this->id;   
    }
    
    public function getTitle() {
        if (isset($this->props['title'])) {
          return $this->props['title'];
      } else {
          return $this->getLocalName();
      }
    }
    
     public function getFileName() {
        
         return $this->getLocalName();  
         
     }

    public function getBaseName() {
        return basename($this->id);
    }

    public function getContentUri() {
        return $this->fulluri;

    }
    public function getContentUriSample() {
        return $this->props['fileuri'];
    }
    public function create() {

    }

    public function getEditors() {
    	
    	$perm = bx_permm::getInstance();
    	$localUri = substr($this->id, 0, strrpos($this->id, '/')+1);
    	$localUri = str_replace('/data//', '/', $localUri);
    	
    	// remove the /files/_galleries prefix
		$localUri = substr($localUri, strrpos($localUri, '/gallery/'));

        $mt = $this->getMimeType();
        if (strpos($mt,"text") === 0) {
        	if(strpos($this->id, ".configxml") !== false or
        		$perm->isAllowed($localUri,array('collection-back-edit_file'))) {
            	return array("oneform","file");
        	}
        }
        if (strpos($mt,"image") === 0) {
	    	$e = array();
	 		if($perm->isAllowed($localUri,array('collection-back-edit_file'))) {
	        	$e[] = "file";
	    	}
	 		if($perm->isAllowed($localUri,array('gallery-back-edit_image'))) {
	        	$e[] = "image";
	    	}
	    	return $e;
        }
        if ($mt != "httpd/unix-directory") {
        	if($perm->isAllowed($localUri,array('collection-back-edit_file'))) {
            	return array("file");
        	}
        }
    }

     public function getLocalName() {
        if (isset($this->props['localname'])) {
            return $this->props['localname'];
        }
        if (isset($this->props['fileuri'])) {
            $b = basename($this->props['fileuri']);

            if (isset($this->props['mimetype']) && $this->props['mimetype'] == "httpd/unix-directory") {
                $this->props['localname'] = $b."/";

            } else {
                $p = bx_collections::getFileParts($b);
                if (!empty($p['number'])) {
                    $this->props['localname'] = $p['name'].'_'.$p['number'];
                } else {
                    $this->props['localname'] = $p['name'];
                }
                if ($p['ext']) {
                   $this->props['localname'] .= '.'.$p['ext'];
                }

            }
            return $this->props['localname'];
        }
        return null;
    }

     public function delete() {
     
         $id = $this->fulluri;
         if (file_exists($id)) {
             
             if (is_dir($id)) {
                 bx_helpers_file::rmdir($id);
             } else {
                 if(!is_writable($id)) {
                     chmod($id, 0666); 
                 }
                 unlink($id);
             }
               if (bx_resourcemanager::removeAllProperties($this->id)) {
                return true;
            }
            return true;
         }
         return false;
     }


     public function copy($to) {
         
         $id = $this->fulluri;
         $fileRoot= $this->getFileRoot($to);
         if (!$fileRoot) {
             bx_log::log("fileRoot not found");
             return false;
         }
         $oriTo = $to;
         $to = basename($to);
         if (file_exists($id)) {
            if (!file_exists($fileRoot)) {
                if (!mkdir($fileRoot,0755,true)) {
                    bx_log::log("Could not mkdir $fileRoot");
                    
                    return false;
                }
            }
            // if enddestination is a directory, adjust accordingly
            if (is_dir($fileRoot.$to)) {
                $to = $to.'/'.basename($id);
                $oriTo = $oriTo.'/'.basename($id);
            }
            if (is_dir($id)) {
                 $d = new DirectoryIterator($id);
                 if (!file_exists($fileRoot.$to)) {
                     if (!mkdir($fileRoot.$to,0755)) {
                         bx_log::log("Could not mkdir $fileRoot.$to");
                         return false;
                     }
                 }
                 foreach ($d as $file) {
                     $filename = $file->getFileName();
                     if ($filename == '.' || $filename == '..' || $filename == '.svn') {
                         continue;
                     }
                     
                     if ($file->isDir()) {
                         $filename = $filename."/";
                     }
                     $f = new bx_resources_file($id.$filename);
                     $f->copy($oriTo."/".$filename);
                 }
                 return true;
            } else {
                if (copy($id,$fileRoot.$to)) {
                    $f = new bx_resources_file($fileRoot.$to);
                    if ($f) {
                        foreach (bx_resourcemanager::getAllProperties($this->id) as $key => $value) {
                            bx_resourcemanager::setProperty($f->id,$value['name'],$value['value'],$value['namespace']);
                        }
                    }
                    return true;
                } else {
                    print ("Could not copy $id to ".$fileRoot.$to);
                    return false;
                }
            }
         }
         print("$id does not exist");
         return false;
     }
     
     protected function getFileRoot($to) {
         $parts = bx_collections::getCollectionAndFileParts($to);
         $coll = $parts['coll'];
         $p = $coll->getPluginByResourceType('file');
         
         if ($p && method_exists($p,"getAbsoluteFileRoot")) {
            return $p->getAbsoluteFileRoot($coll->uri).bx_collections::sanitizeUrl(dirname($parts['rawname'])."/");
         } else {
             return false;
         }
     }
     
    public function onSave() {
        
        $vconfig = $GLOBALS['POOL']->config->getConfProperty('versioning');
        if ($vconfig && !empty($vconfig)) {
            $vers = bx_versioning::versioning($vconfig);
            if ($vers) {
                
                $vers->commit($this->fulluri, '');
            }
        }
    }
    

}
