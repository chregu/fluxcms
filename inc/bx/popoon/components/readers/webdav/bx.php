<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+

    /**
     * Filesystem access using WebDAV
     *
     * @access public
     */
    class HTTP_WebDAV_Server_bx extends HTTP_WebDAV_Server_Filesystem_MDB2
    {
        // request resource 
        private $_bxResource=null;        
        
               /**
         * Serve a webdav request
         *
         * @access public
         * @param  string  
         */
        function ServeRequest($base = false) 
        {
            
            // set root directory, defaults to webserver document root if not set
            if ($base) { 
                $this->base = realpath($base); // TODO throw if not a directory
            } else if(!$this->base) {
                $this->base = $_SERVER['DOCUMENT_ROOT'];
            }
            $this->db = $GLOBALS['POOL']->db;
            
            if (MDB2::isError($this->db)) {
                throw new PopoonDBException( $this->db);
            }
            //mysql_select_db($this->db_name) or die(mysql_error());
            // TODO throw on connection problems

            
            // let the base class do all the work
            parent::ServeRequest();
            // trigger onSave() handler
            $method = strtolower($_SERVER["REQUEST_METHOD"]);
            if ($method == 'put' && (int) $this->_http_status >=200 && (int) $this->_http_status <205) {
                if ($this->_bxResource && method_exists($this->_bxResource, 'onSave')) {
                    $this->_bxResource->onSave();
                }      
            }
        }
         
         /**
         * GET method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function GET(&$options) 
        {

            // get absolute fs path to requested resource
            $fulluri = $options["path"];
            $mode = "output";
            // get language and strip it from fulluri
            list($fulluri, $lang) = bx_collections::getLanguage($fulluri);
            $GLOBALS['POOL']->config->setOutputLanguage($lang);
            $parts = bx_collections::getCollectionAndFileParts($fulluri,$mode);
            $collection = $parts['coll'] ;
            $filename = $parts['name'];
            $ext = $parts['ext'];
            $id = $filename.".".$ext;
            if($collection === FALSE) {
                throw new BxPageNotFoundException($fulluri);
                return false;
            } else {
                $fspath = $collection->getContentUriById($id,true);
            } 
           
            
            
            // sanity check
            
            if (!file_exists($fspath)) {
                throw new BxPageNotFoundException($fulluri);
                return false;
            }
            // detect resource type
            $options['mimetype'] = $this->_mimetype($fspath); 
                
            // detect modification time
            // see rfc2518, section 13.7
            // some clients seem to treat this as a reverse rule
            // requiering a Last-Modified header if the getlastmodified header was set
            $options['mtime'] = filemtime($fspath);
            
            // detect resource size
            
            
            // no need to check result here, it is handled by the base class
            $options['stream'] = fopen($fspath, "r");
            $fstat = fstat( $options['stream'] );
            if ($fstat['size'] > 0) {
                $options['size'] = $fstat['size'];
            }
            return true;
        }

        function _mimetype($fspath) {
            $mimetype= popoon_helpers_mimetypes::getFromFileLocation($fspath);
	
            return $mimetype."; charset=utf-8" ; 
        
        }
            
        /**
         * PROPFIND method handler
         *
         * @param  array  general parameter passing array
         * @param  array  return array for file properties
         * @return bool   true on success
         */
        function PROPFIND(&$options, &$files) 
        {
            
            // get absolute fs path to requested resource
            $fspath = $this->base . $options["path"];
            $path = "";
            if (strpos($options['path'],".") === false && substr($options['path'],-1) != "/") {
                $options['path'] .= "/";
            }
           // bx_helpers_debug::dump_errorlog($options);
            $p = bx_collections::getCollectionAndFileParts($options['path'],"output");
            $coll = $p['coll'];
            // prepare property array
            $files["files"] = array();
            // if we have a filename, then it's not a request for a collection, but a sub resource of this collection
            if ($coll && !($coll->mock)) {
                $files["files"][] = $this->fileinfo($path, $coll);
            } else {
                return false;
            }
            // information for contained resources requested?
             
            if (!empty($options["depth"]) && ($coll instanceof bx_collection))  { 
                
                // TODO check for is_dir() first?
                // make sure path ends with '/'
                if (substr($options["path"],-1) !== "/") { 
                    $options["path"] .= "/";
                }
                
                foreach ($coll->getChildren($p['rawname']) as $child) {
                    $files["files"][] = $this->fileinfo($options["path"],$child);
                }
                
            } 
            

            // ok, all done
            
            //bx_helpers_debug::dump_errorlog($files);
            
            return true;
        }
        
        /**
         * PROPPATCH method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function proppatch(&$options) 
        {
            global $prefs, $tab;

            $msg = "";
            
            $path = $options["path"];
            
            $dir = dirname($path)."/";
            $base = basename($path);
            foreach($options["props"] as $key => $prop) {
                if($prop['ns'] == "DAV:") {
                    $options["props"][$key][$status] = "403 Forbidden";
                } else {
                    //FIXME.. here we should call the res->setProperty
                    bx_resourcemanager::setProperty($options['path'],$prop['name'],$prop['val'], $prop['ns']);
                }
            }
                        
            return "";
        }
        
           /**
         * MKCOL method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function MKCOL($options) 
        {           
            $path = $options["path"];
            //$path = "/s";
            $parent = dirname($path);
            if ($parent != "/"){
                $parent .= '/';
            }
            $name = basename($path);
            
            $coll = bx_collections::getCollection($parent);
            
            $coll->makeCollection($name);
            
            /*if(!file_exists($parent)) {
                return "409 Conflict";
            }

            if(!is_dir($parent)) {
                return "403 Forbidden";
            }

            if( file_exists($parent."/".$name) ) {
                return "405 Method not allowed";
            }
            */

            if(!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                return "415 Unsupported media type";
            }
            
          /*
            if(!$stat) {
                return "403 Forbidden";                 
            }
            */

            return ("201 Created");
        }
        
        function fileinfo($path, $res = NULL) 
        {
            $path = $path.$res->getLocalName();
            $path = str_replace("/./","/",$path);
            // create result array
            $info = array();
            $info["path"]  = $path;    
            $info["props"] = array();
            
            // no special beautified displayname here ...
            $info["props"][] = $this->mkprop("displayname",  $res->getDisplayName());
            
            
            // creation and modification time
            $info["props"][] = $this->mkprop("creationdate",    $res->getCreationDate());
            $info["props"][] = $this->mkprop("getlastmodified", $res->getLastModified());

            // type and size (caller already made sure that path exists)
            if ($res->getMimetype() == "httpd/unix-directory") {
                // directory (WebDAV collection)
                $info["props"][] = $this->mkprop("resourcetype", "collection");
                $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");             
               // $info["props"][] = $this->mkprop("getcontentlength", 0);
            } else {
                // plain file (WebDAV resource)
                $info["props"][] = $this->mkprop("resourcetype", "");
                $info["props"][] = $this->mkprop("getcontenttype", $res->getMimetype());
                $info["props"][] = $this->mkprop("getcontentlength",$res->getContentLength()); //filesize($fspath));
            }

            foreach( $res->getAllProperties() as $row) {
                   $info["props"][] = $this->mkprop($row["namespace"], $row["name"], $row["value"]);  
            }
            
            return $info;
        }
      
        /**
         * PUT method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function PUT(&$options) 
        {
         /*   include_once("popoon/streams/bx.php");
            stream_wrapper_register("bx", "bxStream");
*/
            $fspath =  $options["path"];
/*
            if(!@is_dir(dirname($fspath))) {
                return "409 Conflict";
            }
*/
            //$streamtype = $this->getStreamType($options["path"]);
            $p = bx_collections::getCollectionAndFileParts($options["path"],"output");
            $fspath = $p['coll']->getContentUriById($p['rawname']);
            $options["new"] = ! file_exists($fspath);
            //$options["new"] = false;
            if ($p['coll']) {
                $this->_bxResource = $p['coll']->getChildResourceById($p['rawname']);
                if($options['new']) {
                    $this->_bxResource->create();
                }
            } 
            
            $fp = fopen($fspath, "w");
            if(!$fp) {
                error_log("Could not open $fspath for write");
            }
            
            return $fp;
        }
        
   
    /**
         * COPY method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function copy($options, $del=false) 
        {
            // TODO Property updates still broken (Litmus should detect this?)

            
            if(!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                return "415 Unsupported media type";
            }

            // no copying to different WebDAV Servers yet
            if(isset($options["dest_url"])) {
                return "502 bad gateway";
            }

            $source = $this->base .$options["path"];
            if(!file_exists($source)) return "404 Not found";

            $dest = $this->base . $options["dest"];

            $new = !file_exists($dest);
            $existing_col = false;

            if(!$new) {
                if($del && is_dir($dest)) {
                    if(!$options["overwrite"]) {
                        return "412 precondition failed";
                    }
                    $dest .= basename($source);
                    if(file_exists($dest.basename($source))) {
                        $options["dest"] .= basename($source);
                    } else {
                        $new = true;
                        $existing_col = true;
                    }
                }
            }

            if(!$new) {
                if($options["overwrite"]) {
                    $stat = $this->delete(array("path" => $options["dest"]));
                    if($stat[0] != "2") return $stat; 
                } else {                
                    return "412 precondition failed";
                }
            }

            if (is_dir($source)) {
                // RFC 2518 Section 9.2, last paragraph
                if ($options["depth"] != "infinity") {
                    error_log("---- ".$options["depth"]);
                    return "400 Bad request";
                }
                system(escapeshellcmd("cp -R ".escapeshellarg($source) ." " .  escapeshellarg($dest)));

                if($del) {
                    system(escapeshellcmd("rm -rf ".escapeshellarg($source)) );
                }
            } else {                
                if($del) {
                    @unlink($dest);
                    $query = "DELETE FROM properties WHERE path = '$options[dest]'";
                    $this->db->query($query);
                    rename($source, $dest);
                    $query = "UPDATE properties SET path = '$options[dest]' WHERE path = '$options[path]'";
                    $this->db->query($query);
                } else {
                    if(substr($dest,-1)=="/") $dest = substr($dest,0,-1);
                    copy($source, $dest);
                }
            }

            return ($new && !$existing_col) ? "201 Created" : "204 No Content";         
        }

              
        
        
    }
    
    ?>
