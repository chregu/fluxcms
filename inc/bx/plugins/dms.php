<?php
// class bx_plugins_aggregator extends bx_plugin implements bxIplugin{
    //BX_OPEN_BASEDIR . '/files/_dms/'.
    //header("Content-type: text/xml");
class bx_plugins_dms extends bx_plugin implements bxIplugin{
    
    static public $instance = array();
    
    /**
    The table names
    */
    
    protected $db = null;
    protected $tablePrefix = null;
    protected $rootDir ='';
    
    
    /**
    * plugins are singleton, they only exists once (for different modes)
    *  per request. The $mode stuff isn't really used, but may be in 
    *  future releases.
    */
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_dms($mode);
        } 
        return self::$instance[$mode];
    }
    
    /** 
    * You are not allowed to call the constructor from outside, therefore
    *  it's protected. You have to use getInstance()
    */
    protected function __construct($mode) {
         // Get the global table prefix
        
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        // get the db object
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
        
    }
    
     /*** 
        Action methods. 
        This are called from the bxcms popoon action 
     ***/
    
    /**
    * This function is called by the action to check, if it's a "RealResource"
    *  meaning that it actually has something to display
    * If all plugins in a collection return false, a page not found exception is 
    *  thrown
    * For this plugin, we just assume, it has always "something to say"
    *
    * @param    string  $path   The collectionURI
    * @param    string  $id     The id of the request, 
    *                           returned by getIdByRequest                          
    * @return   bool            If it is a RealResource or not.
    * @see      getIdByRequest 
    */
    public function isRealResource($path , $id) {
        return true;
    }
    
    /**
    * Every plugin has to return a unique id for a request.
    * If we for example are in the collection /links/
    *  and the request is /links/foobar.html, we get
    *  $path = /links/, $name="foobar", $ext="html"
    * If the request is /links/something/foobar.html
    *  and there is no collection "something", then name
    *  is "something/foobar"
    * Usually you should not be too concerned about the extension
    *  since that can be differently, if you do match on different
    *  extensions in .configxml
    *
    * In this example, we just return the filename part and add
    *  .links to it, to make it unique
    *
    * @param    string  $path   The collectionURI
    * @param    string  $name   The filename part of the request
    * @param    string  $ext    The extension part of the request
    * @return   string          A unique id
    */
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        return $name.'.'.$ext;
    }
    
    public function getContentById($path,$id) {
        $this->rootDir = BX_OPEN_BASEDIR.'files/_dms'.$path;
        $dir = $id;
        if(isset($_GET['mkdir'])){
           makeDir($path, $this->rootDir);
       }
        
       $this->mode = $this->getParameter($path,"mode");
       bx_helpers_debug::webdump($this->getParameter($path,"mode"));
       if ($this->mode == 'rss') {
              $dir = '/'.preg_replace("#/*rss.xml$#","",$dir);
       } else {
           $dir = '/'.str_replace("index.html","",$dir);
       }
       
        
        if(is_dir($this->rootDir.$dir)) {
            if ($this->mode == 'rss') {
                $dir .= "/";
            }
            return $this->showDir($dir, $path);
        } else {
               return $this->showFile($dir, $path);
        }
        
        $pDir = pathinfo($dir); 
    }
    
    public function showFile($dir, $path){
       $comment = array();
       $v = $GLOBALS['POOL']->versioning;
       
       $prefix = $GLOBALS['POOL']->config->getTablePrefix();
       $dir = preg_replace("#".$path."#", "", $dir);
       $pDir = pathinfo($dir);
       $parentDir = $pDir["dirname"];
       
       $xml = "<dms type='file'>";
       $xml .= "<trashdir>".BX_WEBROOT.$path.".trash</trashdir>";
       $xml .= "<dir>".$dir."</dir>";
       // Parent Dir or not?
       if($pDir["dirname"] == "http://fluxcms/files".$path){
           $xml .= "<parentdir>Main Directory</parentdir>";
       } else {
           $dirlink = BX_WEBROOT_W . $path.$parentDir;
           $xml .= "<parentdirlink>".$dirlink."</parentdirlink>";
           $xml .= "<parentdir>Parent directory:". $parentDir."</parentdir>";
       }    
       
       if(isset($_GET['copyto'])){
           copyTo($path, $dir, $this->rootDir);
       }
       
       if(isset($_GET['cat'])){
           $cat = $_GET['cat'];
           $filepath = "/files/_dms/".$path.$dir;
           header("Content-type: ".popoon_helpers_mimetypes::getFromFileLocation(BX_OPEN_BASEDIR.$filepath));
           $v->cat($filepath, $_GET['cat']);
           //FIXME ügly ügly ügly
           die();
       }
       
       if(isset($_GET['moveto'])){
           moveTo($path, $dir, $this->rootDir);
       }
       
       $newpath = preg_replace("#/#","",$path);
       
       if (isset($_GET['delete']) and $_GET['delete'] == 1) {
           $this->deleteFrom($path, $dir, $this->rootDir);
       } else {
       preg_match("#.*/#", $dir, $newfile);
        
        $newfile=explode($newfile['0'], $dir);
        $reallink = "files/_dms/".$path.$dir;
        $link = $path.$dir;
        
        $logs = $v->log($reallink);
        
        //do logs
        $xml .= "<logs>";
        
        foreach($logs as $log){
            $xml .= "<log>";
            $xml .= "<title>".$log['paths']['0']['path']."</title>";
            $xml .= "<action>".$log['paths']['0']['action']."</action>";
            $xml .= "<rev>".$log['rev']."</rev>";
            $xml .= "<author>".$log['author']."</author>";
            $xml .= "<msg>".$log['msg']."</msg>";
            $xml .= "<date>".$log['date']."</date>";
            $xml .= "</log>";
        }
        
        $xml .= "</logs>";
        
        //do file
        $xml .= "<item>";
        $xml .= "<file>";
        $filedir = preg_replace("#".$path."#","",$dir);
        $xml .= "<filetype>".filetype($this->rootDir.$filedir)."</filetype>";
        $xml .= "<filesize>".filesize($this->rootDir.$filedir)."</filesize>";
        $xml .= "<filename>".$newfile['1']."</filename>";
        $xml .= "<filelink>".BX_WEBROOT_W.$link."</filelink>";
        $xml .= "<realfilelink>".BX_WEBROOT.$reallink."</realfilelink>";
        
        if(isset($_POST['statusbutton'])) {
            $this->setStatus($path, $dir, $_POST['statusselection']);
        }
        
        $xml .= "<filestatus>".bx_resourcemanager::getProperty(bx_helpers_string::removeDoubleSlashes($path.$dir),"status","dms:")."</filestatus>";
        $xml .= "<img>". BX_WEBROOT ."files/images/datei.png</img>";
        $query = "select * from ".$prefix."dmscomments where uri = '".$newpath.$dir."'";
        $res = $GLOBALS['POOL']->db->query($query);
        //do file comments
        $xml .= "<comments>";
        $rslts = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        foreach($rslts as $row) {
            $xml .="<comment><content>".$row['comment']."</content>";
            $xml .="<title>".$row['title']."</title>";
            $xml .="<author>".$row['name']."</author>";
            $xml .="<date>".$row['date']."</date>";
            $xml .= "</comment>";
        }
        $xml .= "</comments>";        
        $xml .= "</file>";
        $xml .= "</item>";
        
       }
        $xml .= "</dms>";
        $dom = new domDocument();
        $dom->loadXML($xml);
        return $dom;
    }
    
    public function showDir($dir, $path){
        
       $v = $GLOBALS['POOL']->versioning;
       
       if(isset($_GET['zipdownload'])){
           $this->getZIP($path, $dir);
       }
       
       $path = substr($path,0,-1);
       if($checkDir = opendir($this->rootDir.$dir)){
           $cDir = 0;
           $cFile = 0;
           $listDir = array();
           $listFile = array(); 
           while($file = readdir($checkDir)){
                
               if($file != "." && $file != ".."){
                   if(is_dir($this->rootDir.$dir . "/" . $file)){
                       $listDir[$cDir] = $file;
                       $cDir++;
                   } else {
                       $listFile[$cFile] = $file;
                       $cFile++;
                   }
               }
           }
           $pDir = pathinfo($dir);
           $parentDir = $pDir["dirname"];
           $xml = "<dms type='dir'>";
           $xml .= "<trashdir>".BX_WEBROOT.$path."/.trash</trashdir>";
           $xml .= "<dir>".BX_WEBROOT_W.$path.$dir."</dir>";
           $xml .= "<realdir>".$this->rootDir.$dir."</realdir>";
           
           if($dir == "/") {
               $xml .= "<parentdir>Main Directory</parentdir>";
           } else {
               $dirlink = BX_WEBROOT_W . $path.$parentDir;
               $xml .= "<parentdirlink>".$dirlink."</parentdirlink>";
               $xml .= "<parentdir>Parent directory: $parentDir</parentdir>";
           }
           // show the last 10 logs for current folder
           //FIXME ugly
           
           if($this->mode == "rss") {
                $logs = $v->log('/files/_dms'.$path);
                $xml .= "<logs>";
                for($x=0 ; $x<=10 ; $x++) {
                    $xml .= "<log>";
                    $xml .= "<title>".$logs[$x]['paths']['0']['path']."</title>";
                    $xml .= "<action>".$logs[$x]['paths']['0']['action']."</action>";
                    $xml .= "<rev>".$logs[$x]['rev']."</rev>";
                    $xml .= "<author>".$logs[$x]['author']."</author>";
                    $xml .= "<msg>".$logs[$x]['msg']."</msg>";
                    $xml .= "<date>".$logs[$x]['date']."</date>";
                    $xml .= "</log>";
                }
                $xml .= "</logs>";
            }
           // show directories
           if(count($listDir) > 0){
               //sort here laterz
               for($j = 0; $j < count($listDir); $j++){
                   if(0 === strncasecmp($listDir[$j], ".", "1")) {
                       continue;
                   } else {
                           $xml .= "<item>";
                           $xml .= "<folder>";
                           $link = $path.   $dir.$listDir[$j];
                           $xml .= "<foldername>".$listDir[$j]."</foldername>";
                           $xml .= "<foldersize></foldersize>";
                           $xml .= "<filetype>".filetype($this->rootDir.$dir)."</filetype>";
                           $xml .= "<folderlink>".BX_WEBROOT_W.$link."/</folderlink>";
                           $xml .= "<img>". BX_WEBROOT ."files/images/folder.png</img>";
                           $xml .= "</folder>";
                           $xml .= "</item>";
                   }
               }
           }
           // show files
           if(count($listFile) > 0){
               //sort here laterz
               foreach($listFile as $file) {
                    $xml .= "<item>";
                    $xml .= "<file>";
                    $xml .= "<filename>".$file."</filename>";
                    $link = $path .$dir. "/" .$file;
                    $xml .= "<filelink>".BX_WEBROOT_W.$link."</filelink>";
                    $xml .= "<filetype>".filetype($this->rootDir.$dir. "/" . $file)."</filetype>";
                    $xml .= "<filesize>".filesize($this->rootDir.$dir. "/" . $file)."</filesize>";
                    $xml .= "<filestatus>".bx_resourcemanager::getProperty(bx_helpers_string::removeDoubleSlashes($path.$dir. "/" . $file),"status","dms:")."</filestatus>";
                    $xml .= "<img>". BX_WEBROOT ."files/images/datei.png</img>";
                    $xml .= "</file>";
                    $xml .= "</item>";
               }
           }
           $xml .= "</dms>";
           
           closedir($checkDir);
           $dom = new domDocument();
           $dom->loadXML($xml);
           
           return $dom;
       }
    }
    
    
    /***
       UNTIL HERE IS ALL WHAT IT NEEDS FOR A BASIC IMPLEMENTATION
       (to just output a page with all links)
        What follows is additional juice.
     ***/
    
    
    public function getResourceById($path, $id, $mock = false) {
        $pathid = $path.$id;
        if (!isset($this->res[$pathid])) {
            $res = new bx_resources_simple($pathid);
            
            $res->props['outputUri'] = $path.$id;
            $res->props['resourceDescription'] = "dms";
            $res->props['lastmodified'] = date ("d F Y H:i:s.", filemtime(BX_PROJECT_DIR."files/_dms".$path.$id));
            $this->res[$pathid] = $res;
        }
        return $this->res[$pathid];
    }
    
    /***
       admin methods
     ***/  
     
    /**
    * to actually being able to edit links in the admin, we have to return
    *  true here, if the admin actions asks us for that.
    * We don't care about path,id, etc here
    */
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    /**
    * we need to "register" what editors are beeing able to handle this plugin
    */
    
    public function getEditorsById($path, $id) {
        return array();
    }
    
    public function saveComments($data){
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $data['uri'] = preg_replace("#".BX_WEBROOT."#"," ",$data['uri']);
        $query = "insert into ".$prefix."dmscomments (comment, uri, date, title, name) value('".$data['comment']."', '".trim($data['uri'])."', NOW(), 'title', 'gnarf')";
        $GLOBALS['POOL']->db->query($query);
    }
    
    public function handlePublicPost($path, $id, $data) {
        if(isset($data['upload'])){
            move_uploaded_file($_FILES["uploadedfile"]["tmp_name"], $_POST['path'].$_FILES["uploadedfile"]["name"]);
            $this->addFile($_POST['path'].$_FILES["uploadedfile"]["name"], $data['logcomment']);
        }else{                                                        
            if(isset($data['tag'])){
                bx_metaindex::setTags($data['uri'],$data['tag'],true);
            }else{
                $this->saveComments($data);
            }
        }
    }
    
    function addFile($path, $logcomment){
        $id = preg_replace("#".BX_PROJECT_DIR."files/_dms#", "", $path);
        $fileContent = bx_metaindex::callIndexerFromFilename($path, $id);
        //$fnord = strip_tags($fnord);
        $this->setContent($path, $id, $fileContent['bx:']['content']);
        $v = $GLOBALS['POOL']->versioning;
        $addpath = preg_replace('#'.BX_PROJECT_DIR.'#','/',$path);
        $id = preg_replace('#.*/#','/',$path);
        $statuspath = preg_replace('#'.BX_PROJECT_DIR.'files/_dms#','/',$path);
        $this->setStatus($statuspath, $id, "new");
        $v->add($addpath);
        $v->commit($addpath, $logcomment);
    }
    
    
    
    function delFile($path){
        $v = $GLOBALS['POOL']->versioning;
        $delpath = preg_replace('#'.BX_PROJECT_DIR.'#','/',$path);
        unlink(BX_OPEN_BASEDIR."repo/checkout".$delpath);
        $v->delete($delpath);
        $v->commit($delpath, $_GET['dellog']);
    }
    
    function moveTo($path, $dir, $rootDir){
       $v = $GLOBALS['POOL']->versioning;
       $newpath = preg_replace("#/#","", $path);
       $old = $dir;
       $new = $_GET['moveto'];
       $new = preg_replace("#/".$newpath."#","", $new);
       $v->move("/files/_dms/".$path.$old, "/files/_dms/".$path.$new);
       copy($rootDir.$old, $rootDir.$new);      
       unlink($rootDir.$old);
       header("Location: ".BX_WEBROOT . $newpath .$new);
       $query="Update ".$prefix."dmscomments set uri = '".$newpath.$new."' where uri = '".$newpath.$old."'";
       $GLOBALS['POOL']->db->query($query);
       die();
    }
    
    function copyTo($path, $dir, $rootDir){
        $v = $GLOBALS['POOL']->versioning;
        $old = $dir;
        $new = $_GET['copyto'];
        $new = preg_replace("#".$path."#","", $new);
        $v->copy("/files/_dms/".$path.$old, "/files/_dms/".$path.$new);
        
        copy($rootDir.$old, $rootDir.$new);
        
        header("Location: ".BX_WEBROOT_W . $path.$new);
        die();
    }
    
    function deleteFrom($path, $dir, $rootDir) {
        $clearsrc = bx_helpers_string::removeDoubleSlashes($rootDir.$dir);
        copy($clearsrc, BX_PROJECT_DIR."repo/checkout/files/_dms".$path.".trash".$dir);
        copy($rootDir.$dir, $rootDir.".trash/".$dir);
        $this->setStatus($path.".trash/", $dir, "deleted");
        $this->delFile($rootDir.$dir);
        unlink($rootDir.$dir);
        $query="delete from ".$prefix."dmscomments where uri = '".$path.$dir."'";
        $GLOBALS['POOL']->db->query($query);
        header("Location: ".BX_WEBROOT. $path.$parentDir);
        die();
    }
    
    function makeDir($path, $rootDir) {
       $new = $_GET['mkdir'];
       $path = preg_replace("#/#","", $path);
       $new = preg_replace("#".BX_WEBROOT.$path."#","", $new);
       mkdir($rootDir.$new);
       header("Location: ".BX_WEBROOT . "$path".$new);
       die();
    }
    
    function getZip($path, $dir) {
        //$md5 = md5($_GET['zipdownload'].time());
        $tmpname = tempnam($_GET['zipdownload'], "bxzip_");
        $zippath = BX_PROJECT_DIR."files/_dms".$path.$dir.$_GET['zipdownload'];
        bx_helpers_string::removeDoubleSlashes($zippath);
        exec("zip -r ".BX_PROJECT_DIR."files/tmp".$path.$tmpname.".zip ".$zippath) or die(" no way");
        header("Location: ".BX_WEBROOT."files/tmp".$path.$tmpname.".zip");
        die();
    }
    
    function setStatus($path, $dir, $value){
        bx_resourcemanager::setProperty(bx_helpers_string::removeDoubleSlashes($path.$dir),"status",$value,"dms:");       
    }
    
    function setContent($path, $dir, $value){
        bx_resourcemanager::setProperty(bx_helpers_string::removeDoubleSlashes($dir),"content",$value,"bx:");
    }
    
    /*not work yet*/
    function directory_size($nomrep){
       $dossier = opendir(BX_PROJECT_DIR."files/_dms".$nomrep);
       $file_size_up = 0;
       while ($Fichier = @readdir($dossier)){
           if ($Fichier != "." && $Fichier != ".."){
               if ($Fichier){
                   $file_size_up += filesize($nomrep."/".$Fichier);
               }
           }
       }
       @closedir($dossier);
       return $file_size_up;
    }


}

?>
