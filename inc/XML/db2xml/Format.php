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

/**
* @author   Christian Stocker <chregu@liip.ch>
* @version  $Id$
* @package  XML_db2xml
*
*/
Class XML_db2xml_Format {

    /**
    * The DomDocument Object to be used in the whole class
    *
    * @var      object  DomDocument
    * @access    private
    */
    public $xmldoc;


    /**
    * The Root of the domxml object
    * I'm not sure, if we need this as a class variable....
    * could be replaced by domxml_root($this->xmldoc);
    *
    * @var      object DomNode
    * @access    private
    */
    public $xmlroot;

    /**
    * Name of the tag element for rows
    *
    * @var  string
    * @see  insertNewRow()
    */
    public $tagNameRow = "row";

    /**
    * Options to be used in extended Classes (for example in db2xml_ext).
    * They are passed with SetOptions as an array (arrary("user_options" = array());
    *  and can then be accessed with $this->user_options["bla"] from your
    *  extended classes for additional features.
    *  This array is not use in this base class, it's only for passing easy parameters
    *  to extended classes.
    *
    * @var      array
    */
    public $formatOptions = array();

    function XML_db2xml_Format(&$parent)
    {

        $this->parent = &$parent;
    }


    function setXmlDoc ($xmldoc) {
        $this->xmldoc = $xmldoc;
	    $this->setAppendSibling();
    }

    function setXmlRoot ($xmlroot) {
        $this->xmlroot = $xmlroot;
    }


    // these are the functions, which are intended to be overriden in user classes

    /**
    *
    * @param    mixed
    * @return   object  DomNode
    * @access   private
    */
    function insertNewResult(&$metadata)
    {
        if ($this->xmlroot) {
            $newChild = $this->xmldoc->createElement($this->parent->tagNameResult, NULL);
            return $this->xmlroot->appendChild($newChild);
        }
        else
        {
            $this->xmlroot = $this->xmldoc->appendChild($this->xmldoc->createElement($this->tagNameResult));
            //PHP 4.0.6 had $root->name as tagname, check for that here...
            if (!isset($this->xmlroot->{$this->tagname}))
            {
                $this->parent->tagname = "name";
            }
            return $this->xmlroot;
        }
    }


    /**
    *   to be written
    *
    * @param    object DomNode $parent_row
    * @param    mixed $res
    * @param    mixed $key
    * @param    mixed &metadata
    * @return   object DomNode
    * @access private
    */
    function insertNewRow($parent_row, $res, $key, &$metadata)
    {
        return  XML_db2xml::newChild($parent_row,$this->tagNameRow, Null);
    }


    /**
    *   to be written
    *
    * @param    object DomNode $parent
    * @param    mixed $res
    * @param    mixed $key
    * @param    mixed &$metadata
    * @param    mixed &$subrow
    * @return   object DomNode
    * @access private
    */
    function insertNewElement($parent, $res, $key, &$metadata, &$subrow)
    {
        return  XML_db2xml::newChild($parent,$metadata[$key]["name"], $this->parent->xml_encode($res[$key]));
    }


    /**
    *   to be written
    *
    * @param    mixed $key
    * @param    mixed $value
    * @param    mixed &$metadata
    * @access private
    */
    function addTableInfo($key, $value, &$metadata) {

    }
	
	
	function insertNewXmlObject($xmlObject,$xpath = Null)
	{
	       if (is_array($xpath))
            {
                if (isset($xpath["root"]))
                {
                    $root = $xpath["root"];
                }
                if (isset($xpath["namespaces"]))
                {
                    $namespaces = $xpath["namespaces"];
                }
				if (isset($xpath["xpath"]))
				{ 
                	$xpath = $xpath["xpath"];
				}
				else
				{
					unset($xpath);
					}
            }

            $subroot = $this->xmlroot;
            if (isset($root))
            {
                $roots = explode("/",$root);
                foreach ($roots as $rootelement)
                {
                    if ( strlen($rootelement) > 0 )
                    {
                        $subroot = XML_db2xml::newChild($subroot,$rootelement,"");
                    }
                }
            }

            // if no xpath is given, just take the whole object

            if ( !(isset($xpath)))
            {
                //$this->xmlroot->addchild does some strange things when added nodes from xpath.... so this comment helps out
                /* I don't think, we need thaat anymore
                $subroot = $subroot->add_child($this->xmldoc->createComment("Hack in line ".__LINE__));
                */
                    
                $subroot->appendChild($subroot->ownerDocument->importNode($xmlObject->documentElement,true));
                
            }
            else
            {
                //$subroot = $subroot->achild($this->xmldoc->createComment("Hack in line ".__LINE__));

                $xctx = new DomXPath($xmlObject);
				if (isset($namespaces))
                {
                	foreach ($namespaces as $ns)
                    {
		                $xctx->registernamespace($ns[0],$ns[1]);
                	}
				}
                $xnode = $xctx->query($xpath);

                foreach ($xnode as $node)
                {
                    $subroot->appendChild($subroot->ownerDocument->importNode($node,true));
                }
            }
     	
	
	}

    /**
    * Adds a xml string to $this->xmldoc.
    * It's inserted on the same level as a "normal" resultset, means just as a children of <root>
    * if a xpath expression is supplied, it takes that for selecting only part of the xml-file
    *
    * the clean and xpath code works only with php 4.0.7
    * for php4.0.6 :
    * I found no cleaner method than the below one. it's maybe nasty (xmlObject->string->xmlObject),
    *  but it works. If someone knows how to add whole DomNodes to another one, let me know...
    *
    * @param    string xml string
    * @param    mixed xpath  either a string with the xpath expression or an array with "xpath"=>xpath expression  and "root"=tag/subtag/etc, which are the tags to be inserted before the result
    * @access private
    */

    function insertNewXmlString ($string,$xpath = Null)
    {

        //check if we have a recent domxml. otherwise use the workaround...
        $version = explode(".",phpversion());

        $this->insertNewXmlObject(DomDocument::loadXML($string),$xpath);
        
    }

    // end functions, which are intended to be overriden in user classes

    /**
    * This method sets the options for the class
    *  One can only set variables, which are defined at the top of
    *  of this class.
    *
    * @param    array   options to be passed to the class
    * @param    boolean   if the old suboptions should be deleted
    * @access   public
    * @see      $nested,$user_options,$user_tableInfo
    */

    function setOptions($options,$delete = False) {
        //set options
        if (is_array($options))
        {
            foreach ($options as $option => $value)
            {
                if ($option == "user_options") {
                    $option = "formatOptions";
                }
                if (isset($this->{$option}))
                {
                    if (is_array($value) && ! $delete)
                    {
                        foreach ($value as $suboption => $subvalue)
                        {
                            $this->{$option}["$suboption"] = $subvalue;
                        }
                    }
                    else
                    {
                        $this->$option = $value;
                    }
                }
            }
        }
    }

	/**
    * Append_child changed in 4.3 to append_sibling
    * check for that here and set a class-variable
    */
    
	function setAppendSibling()
    {
    	if (method_exists($this->xmldoc,"append_sibling"))
        {
			$this->APPEND_SIBLING="append_sibling";	
		}
        else
        {	
        	$this->APPEND_SIBLING="append_child";
		}
		$this->APPEND_SIBLING;
	}

}

