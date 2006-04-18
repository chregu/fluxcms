<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+


class bx_plugins_file extends bx_plugin implements bxIplugin {

    private static $instance = array();
    private $fileroots = array();
    private $absolutefileroots = array();
    /*** magic methods and functions ***/

    public static function getInstance($mode) {

        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_file($mode);
        }
        return self::$instance[$mode];
    }

    protected function __construct($mode) {
        $this->mode = $mode;

    }

    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        if (file_exists($this->getFileRoot($path).$name.".".$ext)) {
            return $this->getFileRoot($path).$name.".".$ext;
        }
    }

    public function getPipelineParametersById($path, $id) {
      // FIXME, we need another resource reader, it doesn't work if request != id
            return array('pipelineName'=>'resourceReader');
        }
        
    public function isRealResource($path , $id) {
        return true;
    }

    public function getFileRoot($uri) {

        if (!isset($this->fileroots[$uri])) {
            if ($root = $this->getParameter($uri,"virtualDir")) {
                $rootCheck = realpath(BX_OPEN_BASEDIR.$root) ;
                if (BX_OS_WIN) {
                  $rootCheck = str_replace('\\','/',$rootCheck);
                  
                }
                if (strpos($rootCheck,BX_OPEN_BASEDIR) !== 0) {
                    $this->fileroots[$uri] = '__notallowed';
                } else {
                    $this->fileroots[$uri] = $root;
                }
            } else {
                $this->fileroots[$uri] = $uri;
            }
        }
        return $this->fileroots[$uri];
    }

    public function getAbsoluteFileRoot($uri) {

        if (!isset($this->absolutefileroots[$uri])) {
            $id = $this->getFileRoot($uri);
             if (BX_OS_WIN && preg_match("#^[a-zA-Z]:#",$id)) {
                $this->absolutefileroots[$uri] =$id;
            } else  if (substr($id,0,1) == "/") {
                $this->absolutefileroots[$uri] =$id;
            } else {
                $this->absolutefileroots[$uri] = BX_OPEN_BASEDIR.$id;

            }
        }

        return  $this->absolutefileroots[$uri];
    }

    public function getChildren($coll, $id) {
        $root = $this->getFileRoot($coll->uri).$id;
        $virtual = $this->getParameter($coll->uri,"virtualDir");
        if (file_exists(BX_OPEN_BASEDIR.$root)) {
            $dir = new DirectoryIterator(BX_OPEN_BASEDIR.$root);
            $ch = array();
            foreach ($dir  as $file) {

                $name = $file->getFileName();
                if (strpos($name,".") === 0) {
                    continue;
                }
                if ($file->isDir()) {
                    if ($virtual) {
                        $c= new bx_resources_file($root.$name."/");
                        $c->props['mimetype'] = "httpd/unix-directory";
                        $c->uri = str_replace("$virtual",$coll->uri,$c->uri);
                        $ch[] = $c;
                    }
                } else {
                    $ch[] = new bx_resources_file($root.$name);
                }
            }
            return $ch;
        }
        return array();
    }

    public function adminResourceExists ($path, $id, $ext=null, $sample = false) {
        return $this;
    }

    public function getResourceById($path, $id, $mock = false) {
        return new bx_resources_file($this->getFileRoot($path).$id);
    }
    public function handlePOST($path, $id, $data, $mode = null) {
    if ($mode == "FullXML") {

           $res = $this->getResourceById($path,$id);
            if ($res->mock) {
                $res->create();
            }

           $file = $this->getContentUriById($path,$id);
           //FIXME: resource should handle the save, not the plugin, actually..
           //remove dos linefeeds (fucks up svn diffs)
            $data['fullxml'] = preg_replace("#\r\n#","\n",$data['fullxml']);
            if (!file_put_contents($file,bx_helpers_string::utf2entities($data['fullxml']))) {
                print '<span style="color: red;">File '.$file.' could not be written</span>';
                return false;
            } 
            return true;
        }
    }

    public function getResourceTypes() {
        return array('file','archive');
    }

    public function getAddResourceParams($type, $path = null, $options = array()) {   
        $i18n = $GLOBALS['POOL']->i18nadmin;
        $dom = new domDocument();

        $fields = $dom->createElement('fields');

        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'file');
        $nameNode->setAttribute('type', 'file');
        if ($type == 'file') {
            $helpNode = $dom->createElement("help", $i18n->translate("help_uploadfile"));
        } else if ($type == 'archive') {
            $helpNode = $dom->createElement("help", $i18n->translate("help_uploadzip"));
        }
        $nameNode->appendChild($helpNode);
        $fields->appendChild($nameNode);
        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'name');
        $nameNode->setAttribute('type', 'hidden');

        $fields->appendChild($nameNode);

        if ($type == 'image') {
            $sizeNode = $dom->createElement('field');
            $sizeNode->setAttribute('name', 'imagesize');
            $sizeNode->setAttribute('type', 'select');
            $sizes = array("leave", "600","800","1024");
            foreach ($sizes as $size) {
            $sizeOpt = $dom->createElement('option');
            if ($size != "leave") {
                $sizeOpt->setAttribute("name", "max. " . $size ."px width");
            } else {
                $sizeOpt->setAttribute("name", $size);
            }
            $sizeOpt->setAttribute("value", $size);
                $sizeNode->appendChild($sizeOpt);
            }
            $fields->appendChild($sizeNode);
        } else if ($type == 'archive') {
            
            // junk paths?
            $sizeNode = $dom->createElement('field');
            $sizeNode->setAttribute('name', 'junkpaths');
            $sizeNode->setAttribute('type', 'checkbox');
            
            if (isset($options['junkpaths']) && $options['junkpaths']) {
                $sizeNode->setAttribute('checked','checked');
            }
            $sizeNode->setAttribute('textBefore',$i18n->translate("Remove File Paths"));
            $helpNode = $dom->createElement("help", $i18n->translate("help_removefilepath"));
            $sizeNode->appendChild($helpNode);
            $fields->appendChild($sizeNode);
            // fix invalid  paths?
            
            $sizeNode = $dom->createElement('field');
            $sizeNode->setAttribute('name', 'fixinvalid');
            $sizeNode->setAttribute('type', 'checkbox');
            if (isset($options['fixinvalid']) && $options['fixinvalid']) {
                $sizeNode->setAttribute('checked','checked');
            }
            
            $sizeNode->setAttribute('textBefore',$i18n->translate("Fix invalid filenames"));
            $helpNode = $dom->createElement("help", $i18n->translate("help_unlikedfiles"));
            $sizeNode->appendChild($helpNode);
        
            $fields->appendChild($sizeNode);
            
        }
        
        $dom->appendChild($fields);

        return $dom;
    }

    public function addResource($name, $parentUri, $options=array(), $resourceType = null, $returnAfterwards = FALSE) {
        $parts = bx_collections::getCollectionUriAndFileParts($parentUri);
        $rootPath = $this->getFileRoot($parts['colluri']);
        //some IEs send full path instead of just the filename
        // basename() cuts that off
        $filename = basename($_FILES['bx']['name']['plugins']['admin_addresource']['file']);
        $tmpname = $_FILES['bx']['tmp_name']['plugins']['admin_addresource']['file'];
        // prevent illegal filenames
        $filename = bx_helpers_string::makeUri($filename,true);
        $to = BX_OPEN_BASEDIR.$rootPath .$parts['rawname']. $filename;
        $id =( $parentUri. $filename);
        
        self::addFileResource($tmpname,$to, $parentUri,$resourceType,$options,$id);
        $r = $this->getResourceById(substr($parentUri,1), $filename);
        $r->onSave();
        
        if($returnAfterwards == TRUE)
            return $id;
        
        if (isset($_POST['bx']['plugins']['admin_addresource']['redirect'])) {
            if($filename == "none") {
                header("Location: ".$_POST['bx']['plugins']['admin_addresource']['redirect']);
            } else {
                header("Location: ".$_POST['bx']['plugins']['admin_addresource']['redirect']."?fileuri=$parentUri".$filename);
            }
        } else if ($resourceType == "archive") {
            header("Location: ".BX_WEBROOT."admin/addresource/".$parentUri."?type=archive&updateTree=$parentUri");
        } else {
            header("Location: ".BX_WEBROOT."admin/edit/".$id."?updateTree=$parentUri");
        }
        exit(0);
    }
    
    static public function addFileResource($tmpname,$to, $parentUri, $resourceType,$options,$id) {
        if ($GLOBALS['POOL']->config->allowPHPUpload != "true" && strtolower(substr($to,-3)) == "php") {
            $to = $to . ".txt";
        }
        
        if (!file_exists(dirname($to))) {
            mkdir(dirname($to),0755,true);
        }
        move_uploaded_file($tmpname,$to);
        chmod ($to,0644);
        if ($resourceType == "archive") {
            switch (popoon_helpers_mimetypes::getFromFileLocation($to)) {
                case "application/zip":
                    // FIXME: make unzip adjustable...
                    if (isset($options["junkpaths"]) && $options["junkpaths"] == "on") {
                        $junk = " -j ";   
                    } else {
                        $junk = "";
                    }
                    $exec = escapeshellcmd("unzip $junk -o -d ". escapeshellarg( dirname($to)). " ". escapeshellarg($to));
                    exec($exec,$output);
                    if (isset($options["fixinvalid"]) && $options["fixinvalid"] == "on") {
                        array_shift($output);
                        foreach ($output as $fileinfo) {
                            $fileinfo = explode(" ",trim($fileinfo),2);
                            $filename = array_pop($fileinfo);
                            $dirname = dirname($filename);
                            $filename = basename($filename);
                            $filenameFixed = bx_helpers_string::makeUri($filename,true);
                            
                            if ($filenameFixed != $filename) {
                                rename($dirname.'/'.$filename,$dirname.'/'.$filenameFixed);
                            }
                        }   
                        
                    }
                    break;
                case "application/x-gzip":
                    $ar = new Archive_Tar($to,"gz");
                    $ar->extract(dirname($to));
                    break;
                case "application/x-bz2":
                    $ar = new Archive_Tar($to,"bz2");
                    $ar->extract($rootPath.$parts['rawname']);
                    break;
                case "application/x-gtar":
                    $ar = new Archive_Tar($to);
                    $ar->extract($rootPath.$parts['rawname']);
                    break;
                break;
            }
            unlink($to);
            return $to;
        } else {
            
             bx_metaindex::callIndexerFromFilename($to,$id);
        }
        
      
        
}

    public function copyResourceById($path, $id, $to, $move = false) {
        if ($GLOBALS['POOL']->config->allowPHPUpload != "true" && strtolower(substr($to,-3)) == "php") {
            $to = $to . ".txt";
        }
        $r = $this->getResourceById($path, $id);
        $parts = bx_collections::getCollectionAndFileParts($to);
        $toCollUri = $this->getFileRoot(bx_collections::sanitizeUrl($parts['coll']->uri));
        if (($r instanceof bx_resource) &&  method_exists($r,"copy")) {

            return $r->copy($toCollUri.$parts['rawname']);
        }
        return array();
    }

    public function makeCollection($path,$new) {
        $filepath = $this->getAbsoluteFileRoot($path);
        bx_helpers_file::mkpath($filepath."/".$new);
        return true;

    }

}
