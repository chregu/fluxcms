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
include_once('XML/db2xml/Format.php');

/**
 *  This class shows with an example, how the base db2xml-class
 *   could be extended.
 *
 * Usage example
 *
 * include_once("XML/db2xml/db2xml_ext.php");
 * $options= array( formatOptions => array (xml_seperator =>"_",
 *                                       element_id => "id"),
 * );
 * $db2xml = new xml_db2xml_ext("mysql://root@localhost/xmltest");
 * $db2xml->Format->SetOptions($options);
 * $xmlstring = $db2xml->getxml("select * from bands");

 * more examples and outputs on
 *   http://php.chregu.tv/db2xml/
 *   for the time being
 *
 * @author   Christian Stocker <chregu@liip.ch>
 * @version  $Id$
 * @package  XML_db2xml
 */

Class XML_db2xml_Format_Extended extends XML_db2xml_Format {


    function XML_db2xml_Format_Extended(&$parent)
    {
        $this->parent = &$parent;
        $formatOptions = array (
                             'xml_seperator' =>'_',
                             'element_id' => 'id',
                             'print_empty_ids' => True,
                             'selected_id' => array(),
                             'field_translate' => array(),
                             'attributes' => array(),
                             'TableNameForRowTags' => True,
                             'replaceStrings' => false, //does replace strings (with string_replace) in results from DB
                         );

        $this->setOptions(array('formatOptions'=>$formatOptions));

    }

    /*
    * @param  $dsn string with PEAR::DB "data source name" or object DB object
    * @param  $root string of the name of the xml-doc root element.
    * @access public
    * @see  XML_db2xml::XML_db2xml()
    */

    function insertNewRow ($parent_row, $res, $key, &$tableInfo)
    {
        if (!isset($tableInfo[$key]['table']) || !$tableInfo[$key]['table'] ) {
            $tableInfo[$key]['table'] = $this->parent->tagNameResult;
        }
        if ( $this->formatOptions['element_id'] && !(isset($tableInfo['id']) && @$res[$tableInfo['id'][$tableInfo[$key]['table']]]) && !$this->formatOptions['print_empty_ids'])
        {
            return Null;
        }

        if ( !$this->formatOptions['TableNameForRowTags'])
        {
            $new_row= XML_db2xml::newChild($parent_row,$this->tagNameRow,Null);
        }
        else
        {
            $new_row= XML_db2xml::newChild($parent_row,$tableInfo[$key]['table'],Null);
        }
        /* make an unique ID attribute in the row element with tablename.id if there's an id
               otherwise just make an unique id with the php-function, just that there's a unique id for this row.
                CAUTION: This ID changes every time ;) (if no id from db-table)
               */
//        $this->SetAttribute($new_row,'type','row');

        if (isset($tableInfo['id']) && @$res[$tableInfo['id'][$tableInfo[$key]['table']]])
        {
            $new_row->setAttribute('id', $tableInfo[$key]['table'] . $res[$tableInfo['id'][$tableInfo[$key]['table']]]);
        }
        else
        {
            if (!isset($this->IDcounter[$tableInfo[$key]['table']]))
            {
                $this->IDcounter[$tableInfo[$key]['table']] = 0;
            }
            $this->IDcounter[$tableInfo[$key]['table']]++;
            $new_row->setAttribute('id', $tableInfo[$key]['table'].$this->IDcounter[$tableInfo[$key]['table']]);

        }

        return $new_row;
    }


    function insertNewResult (&$tableInfo) {

        if (isset($this->formatOptions['result_root'])) {
            $result_root = $this->formatOptions['result_root'];
        }
        elseif (isset($tableInfo[0]['table']))
        $result_root = $tableInfo[0]['table'];
        else
            $result_root = $this->parent->tagNameResult;

        if ($this->parent->Format->xmlroot)
            $xmlroot = XML_db2xml::newChild($this->parent->Format->xmlroot,$result_root,Null);
        else
            $xmlroot= XML_db2xml::addRoot($this->parent->Format->xmldoc,$result_root);

//        $this->SetAttribute($xmlroot,'type','resultset');

        return $xmlroot;
    }


    function insertNewElement ($parent, $res, $key, &$tableInfo, &$subrow) {

        if ($this->formatOptions['xml_seperator']) {
            // initialize some variables to get rid of warning messages
            $beforetags = '';
            $before[-1] = Null;
            $i = 0;
            if (! isset($tableInfo[$key]['tags'])) {
                $_expl = explode($this->formatOptions['xml_seperator'],str_replace('<','-',$tableInfo[$key]['name']));

                if (is_numeric(substr($_expl[0],0,1)))
                {
                	$_expl[0]= 'int_'.$_expl[0];
	            }		
                $tableInfo[$key]['tags'] = $_expl;
                $tableInfo[$key]['count'] = count($_expl) - 1;
            } 
            
            $regs = $tableInfo[$key]['tags'];

            if (isset($regs[-1]))
            {
                $subrow[$regs[-1]] = $parent;
            } else {
                $subrow[Null] = $parent;
            }
            // here we separate db fields to subtags.
          
          //$count = 0;
            
            $c = $tableInfo[$key]['count'];
            for ($i = 0; $i < $c; $i++)
            {
                $beforetags .=$regs[$i].'_';
                $before[$i] = $beforetags;
                if ( ! isset($subrow[$before[$i]]) ) {
                    $subrow[$before[$i]] = XML_db2xml::newChild($subrow[$before[$i - 1]],$regs[$i], NULL);
                }
            }
            
        $subrows = XML_db2xml::newChild($subrow[$before[$i - 1]],$regs[$i], $this->parent->xml_encode($res[$key],$this->formatOptions['replaceStrings']));

        }
        else
        {
            if (is_numeric($tableInfo[$key]['name']))
            {
		        $tableInfo[$key]['name'] = 'int_'.$tableInfo[$key]['name'];
	        }		
            $subrow=XML_db2xml::newChild($parent,$tableInfo[$key]['name'], $this->parent->xml_encode($res[$key],$this->formatOptions['replaceStrings']));
        }

    }

    function addTableinfo($key, $value, &$tableInfo) {

        if (isset($value['table']) && !isset($tableInfo['id'][$value['table']]) && $value['name'] == $this->formatOptions['element_id'] )
        {
            $tableInfo['id'][$value['table']]= $key;
        }
        if (isset($value['name']) && isset($this->formatOptions['field_translate'][$value['name']])) {
            $tableInfo[$key]['name'] = $this->formatOptions['field_translate'][$value['name']];
        }
    }

    function SetResultRootTag ($resultroot)
    {
        if (isset($resultroot))
        {
            $options = array('formatOptions' => array('result_root'=>$resultroot));
            $this->setoptions($options);
        }
    }

    function unsetResultRootTag ()
    {
        unset ($this->formatOptions['result_root']);
    }
}
