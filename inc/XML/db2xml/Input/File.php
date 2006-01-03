<?php
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@phant.ch>                         |
// +----------------------------------------------------------------------+
//
// $Id$

function addTestBefore_File($resultset)  {
       return (is_string($resultset) && preg_match("/\.xml$/",$resultset));
}



Class XML_db2xml_Input_File {


    function XML_db2xml_Input_File (&$parent)
    {
        $this->parent = &$parent;
    }

    /**
    * Adds the content of a xml-file to $this->xmldoc, on the same level
    * as a normal resultset (mostly just below <root>)
    *
    * @param    string filename
    * @param    mixed xpath  either a string with the xpath expression or an array with "xpath"=>xpath expression  and "root"=tag/subtag/etc, which are the tags to be inserted before the result
    * @access   public
    * @see      doXmlString2Xml()
    */
    function add($file,$xpath = Null)
    {

    /*    $fd = fopen( $file, "r" );
        $content = fread( $fd, filesize( $file ) );
        fclose( $fd );
	*/
    
	if (file_exists($file)) {
        $dom = new DomDocument();
        $dom->load($file);
        $this->parent->Format->InsertNewXmlObject($dom,$xpath);
	}

    }
}
