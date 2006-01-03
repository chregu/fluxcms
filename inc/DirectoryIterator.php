<?php
// +----------------------------------------------------------------------+
// | DirectoryIterator.php                                                |                                                                    |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Mediagonal Ag                                     |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Hannes Gassert <hannes.gassert@mediagonal.ch>                |
// +----------------------------------------------------------------------+


/**
* A lousy and incomplete port of DirectoryIterator of ext/spl.
* 
* @author Hannes Gassert <hannes@mediagonal.ch>
* @example $d = new DirectoryIterator('.'); foreach ($d as $entry) { echo $entry , "\n<br>"; }
*/
class DirectoryIterator {

    /**
    * @var array
    */    
    protected $_children = array();

    /**
    * @var string
    */
    protected $_path;

    /**
    * @var int
    */
    protected $_curIdx = 0;

    /**
    * @var boolean
    */
    protected $_isDot = false;

    /**
    * @param string Path to directory
    * @param boolean isDot - for internal use only
    */
    public function __construct($path, $isDot = false) {

        $this->_path = realpath($path);
        
        if (is_dir($this->_path)) {
            $dirArray = scandir($this->_path);
        } else {
            $dirArray = null;
        }

        if (is_array($dirArray)) {

            foreach ($dirArray as $i => $entry) {

                $newPath = $this->_path .'/'. $entry;

                $newPathPropName = 'dir_' . md5($newPath);

                // "dot files" -> don't go into recursion.
                if($entry == '.' || $entry == '..' || realpath($newPath) == $this->_path){
                    $newNode = clone $this;
                    $newNode->_isDot = true;
                    $newNode->_path = realpath($newPath);
                }
                else{
                    $thisClass = get_class($this); //necesseray in order to work with classes inheriting from DirectoryIterator..
                    $newNode = new $thisClass($newPath);
                }

                $this->_children[] = $newPathPropName;

                // enable foreach access.. rather hackish.
                $this->{$newPathPropName} = $newNode;

            }
        }
    }

    /**
    * @return string
    */
    public function __toString() {
        return $this->getFileName();
    }

    /**
    * @return string
    */
    public function getFileName() {
        return basename($this->_path);
    }

    /**
    * @return string
    */
    public function getPathName() {
        return $this->_path;
    }

    /**
    * @return boolean
    */
    public function isDir() {
        return is_dir($this->_path);
    }

    /**
    * @return boolean
    */
    public function isFile() {
        return is_file($this->_path);
    }

    /**
    * @return boolean
    */
    public function isLink() {
        return is_link($this->_path);
    }

    /**
    * @return boolean
    */
    public function isExecutable() {
        return is_executable($this->_path);
    }

    /**
    * @return boolean
    */
    public function isWriteable() {
        return is_writeable($this->_path);
    }

    /**
    * @return string
    */
    public function getPerms() {
        return fileperms($this->_path);
    }

    /**
    * @return int
    */
    public function getAtime() {
        return fileatime($this->_path);
    }

    /**
    * @return int
    */
    public function getCtime() {
        return filectime($this->_path);
    }

    /**
    * @return int
    */
    public function getMtime() {
        return filemtime($this->_path);
    }

    /**
    * @return int
    */
    public function getInode() {
        return fileinode($this->_path);
    }

    /**
    * @return int
    */
    public function getSize() {
        return filesize($this->_path);
    }

    /**
    * @return string
    */    
    public function getOwner() {
        return fileowner($this->_path);
    }


    /**
    * @return string
    */
    public function getGroup() {
        return filegroup($this->_path);
    }

    /**
    * @return boolean
    */
    public function isDot(){
        return $this->_isDot;
    }

    /**
    * @return boolean
    */
    public function isReadable(){
        return is_readable($this->_path);
    }

    /**
    * @return int
    */
    public function key() {
        return $this->_curIdx;
    }

    /**
    * @return object DirectoryIterator
    */
    public function getChildren() {
        if (empty($this->_children)) {
            return false;
        }
        return $this; //ähh.. correct?
    }

    /**
    * @return boolean
    */
    public function valid() {
        return isset($this->_children[$this->_curIdx + 1]);
    }

    public function rewind() {
        $this->_curIdx = 0;
    }

    public function next() {
        $this->_curIdx++;
    }

    /**
    * @return object DirectoryIterator
    */
    public function current() {
        if (isset($this->_children[$this->_curIdx])) {
            return $this->{$this->_children[$this->_curIdx]};
        }
        else {
            return false;
        }
    }

}

/*
$d = new DirectoryIterator('.'); foreach ($d as $entry) { echo $entry , "\n<br>"; }
*/

?>
