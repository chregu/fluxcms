<?php
class bx_plugins_gallery_file {
	
    static function getInstance($dom,$path,$id) {
            return new bx_plugins_gallery_file($dom,$path,$id);
    }
    
    function __construct ($dom,$path, $id) {
        $this->root = $id;
        $this->dom = $dom;
        
    }
    
    function getImagesAndAlbums(& $options) {
	   $dir = new ImageDirectoryIterator($options['root']);
	   $files = array();
        foreach ($dir as $file) {
            $f = new StdClass(); 
            $f->name = $file->getFileName();
            $f->isDot = $file->isDot();
            $f->isDir = $file->isDir();
            $f->isReadable = $file->isReadable();
            $f->isImage = $file->isImage();
            $files[$f->name] = $f;
        }
        ksort($files);
        foreach ($files as $file) {
            $name = $file->name;
            $lang = $GLOBALS['POOL']->config->getOutputLanguage();
            if(!$file->isDot && $file->isReadable && substr($name,0,1) !== ".") {
                if ($file->isDir) {
                    $node = $this->dom->createElement('album');
                    $node->setAttribute('name', $name);
                    $node->setAttribute('href', $name.'/');
                    $prefix = $GLOBALS['POOL']->config->getTablePrefix();
                    $subgallery = "/".$options['path'].$name."/";
                    
                    $query = "select * from ".$prefix."properties where path like '".$subgallery."%' and name = 'preview' and value = '1'";
                    foreach ( $GLOBALS['POOL']->db->queryCol($query) as $pic) {
                        $pic = str_replace($subgallery,"",$pic);
                        
                        if (strpos($pic,"/") === false) {
                            $node->setAttribute("preview",$pic);
                        }
                    }
                    $options['albums']->appendChild($node);
                    
                    
                    $options['numberOfAlbums']++;
                } else if ($file->isImage) {
                    if(($options['mode']=='image' ) || ($options['numberOfImages'] + 1 > ($options['currentPage'] - 1) * $options['imagesPerPage']) && ($options['numberOfImages'] + 1<= ($options['currentPage']) * $options['imagesPerPage'])) {
                        $node = $this->dom->createElement('image');
                        $node->setAttribute('href', $name);
                        $node->setAttribute('id', $name);
                        //bx_helpers_debug::webdump($options['path'].$name);
                        /* this code would allow captions and title in overviews as well... */
                        $preview = bx_resourcemanager::getProperty("/".$options['path'].$name,"preview",'bx:'.$lang);
                        //bx_helpers_debug::webdump($preview);
                        if ($preview) {
                            $node->appendChild($this->dom->createTextNode(html_entity_decode($preview,ENT_COMPAT,"UTF-8")));
                        }
                            
                        if ($options['mode'] != 'image' && $options['descriptionInOverview']) {
                            
                            $description = bx_resourcemanager::getProperty("/".$options['path'],"description",'bx:'.$lang);
                            if ($description) {
                                $node->appendChild($this->dom->createTextNode(html_entity_decode($description,ENT_COMPAT,"UTF-8")));
                            }
                        }
                        
                        if ($options['mode'] != 'image' && $options['titleInOverview']) {
                            $title = bx_resourcemanager::getProperty("/".$options['path'].$name,"title",'bx:'.$lang);
                            if ($title) {
                                
                                $node->setAttribute('imageTitle', html_entity_decode($title,ENT_COMPAT,"UTF-8"));
                            }
                        }
                        
                        
                        if ($name == $options['basename']) {
                            $title = bx_resourcemanager::getProperty("/".$options['path'].$name,"title",'bx:'.$lang);
                            $description = bx_resourcemanager::getProperty("/".$options['path'].$name,"description",'bx:'.$lang);
                            
                            if ($title) {
                                $title = html_entity_decode($title,ENT_COMPAT,"UTF-8");
                                $this->dom->documentElement->setAttribute('imageTitle', $title);
                            
                            }
                            if ($description) {
                                $description = html_entity_decode($description,ENT_COMPAT,"UTF-8");
                                $this->dom->documentElement->setAttribute('imageDescription', $description);
                            
                            }
                            $this->dom->documentElement->setAttribute('imageHref', $name);
                            $this->dom->documentElement->setAttribute('imageId', $name);
                            
                           $options['numberOfCurrentImage'] = $options['numberOfImages'] + 1 ;
                           
                           $this->dom->documentElement->setAttribute('numberOfCurrentImage', $options['numberOfCurrentImage']);
                           
                        }
                        
                        $options['images']->appendChild($node);
                    }
                    $options['numberOfImages']++;
                }
            }
        }
       return true;
    }
    
}
