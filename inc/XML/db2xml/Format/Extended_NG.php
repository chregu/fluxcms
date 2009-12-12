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
include_once('XML/db2xml/Format/Extended.php');

/**
 *  This class shows with an example, how the base db2xml-class
 *   could be extended.
 *
 * Usage example
 *
 * include_once('XML/db2xml/db2xml_ext.php');
 * $options= array( formatOptions => array (xml_seperator =>'_',
 *                                       element_id => 'id'),
 * );
 * $db2xml = new xml_db2xml_ext('mysql://root@localhost/xmltest');
 * $db2xml->SetOptions($options);
 * $xmlstring = $db2xml->getxml('select * from bands');

 * more examples and outputs on
 *   http://php.chregu.tv/db2xml/
 *   for the time being
 *
 * @author   Christian Stocker <chregu@liip.ch>
 * @version  $Id$
 * @package  XML_db2xml
 */


Class XML_db2xml_Format_Extended_NG extends XML_db2xml_Format_Extended {


    function XML_db2xml_Format_Extended_NG(&$parent)
    {
        $this->parent = &$parent;
        $formatOptions = array (
                             'xml_seperator' =>'_',
                             'element_id' => 'ID',
                             'print_empty_ids' => True,
                             'selected_id' => array(),
                             'field_translate' => array(),
                             'attributes' => array(),
                             'TableNameForRowTags' => True
                         );

        $this->setOptions(array('formatOptions'=>$formatOptions));

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
            $xmlroot = $this->parent->Format->xmlroot->new_child($result_root,Null);
        else
            $xmlroot= $this->parent->Format->xmldoc->add_root($result_root);

//        $this->SetAttribute($xmlroot,'type','resultset');


            $this->xmldoc = $this->parent->Format->xmldoc;
            $this->docfrag = $this->xmldoc->create_document_fragment();

        return $xmlroot;
    }


    function insertNewElements ($parent, $content) {

            if ($content) {
                $this->docfrag->open_mem(($content));
                if ($parent) {

                    $new_elements= $parent->append_child($this->docfrag);
                    return $parent;
                    
                }
            }
    }


    function insertNewElement ($parent, $res, $key, &$tableInfo, &$subrow) {

        if ($this->formatOptions['xml_seperator'] )
        {
            //only explode it once, if at all, starTag should be set in Dbresult.php already
            if (! isset($tableInfo[$key]['startTag'])) {
                $_expl = explode($this->formatOptions['xml_seperator'],str_replace('<','-',$tableInfo[$key]['name']));

                if (is_numeric(substr($_expl[0],0,1)))
                {
                	$_expl[0]= 'int_'.$_expl[0];
	            }		
                $tableInfo[$key]['startTag'] = '<'.implode('><',$_expl).'>';
                $tableInfo[$key]['endTag'] = '</'.implode('></',array_reverse($_expl)).'>';
            }
            $this->docfrag->open_mem($tableInfo[$key]['startTag'].utf8_encode($res[$key]).$tableInfo[$key]['endTag']);
            $parent->append_child($this->docfrag);
        }
        else
        {
            if (is_int($tableInfo[$key]['name']))
            {
            	$tableInfo[$key]['name'] = 'int_'.$tableInfo[$key]['name'];
	        }		
            $this->docfrag->open_mem('<'.$tableInfo[$key]['name'].'>'.utf8_encode($res[$key]).'</'.$tableInfo[$key]['name'].'>');            
            $parent->append_child($this->docfrag);
        }

    }
}
