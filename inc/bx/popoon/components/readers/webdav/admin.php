<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
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
class HTTP_WebDAV_Server_admin extends HTTP_WebDAV_Server_Filesystem_MDB2
{
    public $dirs = array("themes","xml","files","structure","forms");
    public $excludeFiles = ".svn";
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
    }
    
    function GET(&$options) 
    {
        
        // get absolute fs path to requested resource
        if ($this->checkPath($options["path"])) {
            return parent::GET($options);
        } else {
           return false;
        }
        
    }
    
    function fileinfo($path) {
        if ($this->checkPath($path)) {
            return parent::fileinfo($path);
        } else {
            return false;
        }
    }
    
    function checkPath($path) {
        
        //  bx_helpers_debug::dump_errorlog($path);
        if ($path == "/") {
            return true;
        }
        if (strpos($path,$this->excludeFiles) !== false) {
            return false;
        }
        
        if ($pos = strpos($path,"/",1)) {
            $rootDir = substr($path,1,$pos - 1);   
        } else {
            $rootDir = substr($path,1 );
        }
        
        if (in_array($rootDir,$this->dirs)) {
            return true;
        }
        
        return false;
        
    }
    
    
    
    
    
}

?>
