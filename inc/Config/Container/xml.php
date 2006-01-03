<?php
//+----------------------------------------------------------------------+
// | PHP Version 4                                                       |
//+----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group            |
//+----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is       |
// | available at through the world-wide-web at                          |
// | http://www.php.net/license/2_02.txt.                                |
// | If you did not receive a copy of the PHP license and areunable to   |
// | obtain it through the world-wide-web, please send a note to         |
// | license@php.net so we can mail you a copy immediately.              |
//+----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@phant.ch>                        |
// |          Alexander Merz <alexander.merz@t-online.de>                |
//+----------------------------------------------------------------------+
//

require_once( "Config/Container.php" ) ;

/**
* Config-API-Implemtentation for XML-Files
*
* This class implements the Config-API based on ConfigDefault
*
* @author      Christian Stocker <chregu@nomad.ch>
* @access      public
* @version     Config_xml.php, 2000/04/16
* @package     Config
*/


class Config_Container_xml extends Config_Container {

    /**
    * contains the features given by parseInput
    * @var array
    * @see parseInput()
    */
    var $feature = array ("IncludeContent" => True,
        "MasterAttribute" => False,
        "IncludeMasterAttribute" => True,
        "IncludeChildren" => True,
        "KeyAttribute" => False      
    );

    
    var $allowed_options = array();
    /**
    * parses the input of the given data source
    *
    * The Data Source is a file, so datasrc requires a existing file.
    * The feature-array have to contain the comment char array("cc" => Comment char)
    *
    * @access public
    * @param string $datasrc  Name of the datasource to parse
    * @param array $feature   Contains a hash of features
    * @return mixed             returns a PEAR_ERROR, if error occurs
    */

    function parseInput( $datasrc = "",$feature = array() )
    {
        $this -> datasrc = $datasrc ;
        $this->setFeatures($feature,  array_merge($this->allowed_options, array('IncludeContent', 'MasterAttribute','IncludeMasterAttribute','IncludeChildren','KeyAttribute')));
        //PHP 5.0.3 has a serious bug. we can remove that code, in some months again, when nobody's using 5.0.3 anyway ;)
        $version = phpversion();
        if (version_compare ($version,"5.0.3","<=") && version_compare($version,"5.0.3RC1",">=")) {
	    print "From ". __FILE__.":".__LINE__."<br/>\n";
            die("PHP 5.0.3 has a serious bug somewhere in the foreach code. This code makes the xml Config parser (and other stuff) very unstable. <br/>\nPlease up- or downgrade");   
        }
        if( file_exists( $datasrc ) )
        {
            $xml = new DomDocument();
            $xml->load( $datasrc );
            
            $root = $xml->documentElement;
            $this->addAttributes($root);
            $this->parseElement($root,"/".$root->localName);
        }
        else
        {
            return new PEAR_Error( "File '".$datasrc."' doesn't
                                   exists!", 31, PEAR_ERROR_RETURN, null, null );
        }
    } // end func parseInput

    /**
    * parses the input of the XML_ELEMENT_NODE into $this->data
    *
    * @access private
    * @param object XML_ELEMENT_NODE $element
    * @param string $parent xpath of parent ELEMENT_NODE
    */

    function parseElement ($element,$parent = "/") {
        foreach($element->childNodes as $tag)
        {
            
            if ( XML_ELEMENT_NODE == $tag->nodeType)
            {
               
                //if feature KeyAttribute is set and the name is an attribute in the xml, take this as key for the array
                if ($this->feature["KeyAttribute"] && $tag->getAttribute($this->feature["KeyAttribute"]))
                {
                    $tag->name = $tag->getAttribute($this->feature["KeyAttribute"]);
                } else {
                    $tag->name = $tag->localName;
                }
                
                $this->addAttributes($tag,$parent);
              
                if ($tag->hasChildNodes())
                {
                   
                    $this->parseElement($tag,$parent."/".$tag->name);
                }
            }
        }
    }
    //end func parseElement

    /**
    * ?? ask Christian
    *
    * @access private
    * @param string             $element    the element to add perhaps?
    * @param object I_dont_know $parent     the parent element?
    */


    function addAttributes($element,$parent="")
    {
	    $parentslash =""; //E_ALL fix
        if ($parent=="") {
                       //this is only for the root element
                        $parentslash ="/";
        }


        if ($this->feature["IncludeChildren"] )
        {
            if (!isset($element->name)) {
                $element->name = $element->localName;
            }
            $this->data["$parent"."$parentslash"]["children"][] = $element->name;
        }
        
	    $element->content = trim($element->nodeValue);
            
        if (($this->feature["IncludeContent"]|| $this->feature["MasterAttribute"] == "content") && $element->content)
        {
                   
            if ($this->feature["MasterAttribute"] == "content")
            {
                   $this->data["$parent"."$parentslash"][$element->name] =$element->content;
            }
            if ($this->feature["IncludeMasterAttribute"] || $this->feature["MasterAttribute"] != "content")
            {
                $this->data["$parent/".$element->name]["content"] =$element->content;
            }
        }
         
            foreach ($element->attributes as $attribute)
            {
                if ($this->feature["MasterAttribute"] && $attribute->name == $this->feature["MasterAttribute"])
                {
                    $this->data[$parent."$parentslash"][$element->name] = $element->getAttribute($attribute->name);
                }
                if ($this->feature["IncludeMasterAttribute"] || $attribute->name != $this->feature["MasterAttribute"])
                {
                    $this->data["$parent/".$element->name][$attribute->name] = $element->getAttribute($attribute->name);
                }
            }
       
    }
    //endfunc addAttributes
};


?>
