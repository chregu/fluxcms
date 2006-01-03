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

function addTestBefore_Array($resultset)  {
       return is_array($resultset);
}   
    /*
    * @author   Christian Stocker <chregu@bitflux.ch>
    * @version  $Id$
    * @package  XML_db2xml            
    */

Class XML_db2xml_Input_Array {



    function XML_db2xml_Input_Array (&$parent)
    {
        $this->parent = &$parent;
    }

    /**
    * Adds an aditional resultset generated from an Array
    *  to $this->xmldoc
    * TODO: more explanation, how arrays are transferred
    *
    * @param    array multidimensional array.
    * @access   public
    * @see      doArray2Xml()
    */
    function add ($array)
    {
        $parent_row = $this->parent->Format->insertNewResult($metadata);
        $this->DoArray2Xml($array,$parent_row);
    }

    /**
    * For adding whole arrays to $this->xmldoc
    *
    * @param    array
    * @param    Object domNode
    * @access   private
    * @see      addArray()
    */

    function DoArray2Xml ($array, $parent) {

        foreach ($array as $key => $val)
            {
                $tableInfo[$key]["table"] = $this->parent->tagNameResult;
                $tableInfo[$key]["name"] = $key;
            }

        if ($this->parent->user_tableInfo)
        {
            $tableInfo = $this->parent->array_merge_clobber($tableInfo,$this->parent->user_tableInfo);
        }
        foreach ($array as $key=>$value)
        {
            if (is_array($value) ) {
                if (is_int($key) )
                {
                    $valuenew = array_slice($value,0,1);
                    $keynew = array_keys($valuenew);
                    $keynew = $keynew[0];
                }
                else
                {

                    $valuenew = $value;
                    $keynew = $key;
                }
                if (isset($tableInfo[$keynew]["table"] ) && ($tableInfo[$keynew]["table"] == $this->parent->tagNameResult))
                {
                    $tableInfo[$keynew]["table"] = $keynew;
                }
                $rec2 = $this->parent->Format->insertNewRow($parent, $valuenew, $keynew, $tableInfo);
                $this->DoArray2xml($value,$rec2);
            }
            else {
                $this->parent->Format->insertNewElement($parent, $array, $key, $tableInfo,$subrow);
            }
        }

    }



}
