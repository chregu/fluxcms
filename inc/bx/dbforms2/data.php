<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_data {

    /**
     *  Returns a DOMDocument object which contains the data queried from the
     *  database by the passed query.
     *
     *  @param  string $query SQL query to be run.
     *  @access public
     *  @return object DOMObject containing the data returned from the query.
     */
    public static function getXMLByQuery($query, $fromMaster = false) {
        if ($fromMaster) {
            $xml = new XML_db2xml($GLOBALS['POOL']->dbwrite, 'data', 'Extended');
        } else {
            $xml = new XML_db2xml($GLOBALS['POOL']->db, 'data', 'Extended');
        }
        $options = array(
            'formatOptions' => array ( 'xml_seperator' => '')
        );

        $xml->Format->SetOptions($options);
        $xml->add($query);
        $dom = $xml->getXMLObject();
        
        return $dom;
        
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function getValuesByXML($form, $xml) {
        $values = array();
        
        $dataNodeName = $form->tablePrefix.$form->name;
        $xp = new DOMXPath($xml);
        $dataNS = $xp->query("/data/$dataNodeName/$dataNodeName/*");

        foreach($dataNS as $dataNode) {
            $values[$dataNode->tagName] = self::translateValue($dataNode);
        }
        return $values;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected static function translateValue($dataNode) {
        if ($dataNode->childNodes->length == 1) {
            $childNode = $dataNode->firstChild;
            if ($childNode->nodeType == 1) {
                if ($childNode->nodeName == "values") {
                    $values= array();
                    $child = $childNode->firstChild;
                    while ($child) {
                        $values[$child->getAttribute("id")] = $child->textContent;
                        $child = $child->nextSibling;
                    }
                    return $values;
                } else {
                    return $childNode;
                }
            } 
        }
        return $dataNode->textContent;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function addAdditionalDataByForm($form, $xml) {
        
        $xp = new domxpath($xml);
        $dataNodeName = $form->tablePrefix.$form->tableName;
        
        foreach($form->fields as $field) {
            $data = $field->getAdditionalData($form->currentID);
            if ($data) {
                
                $res = $xp->query("/data/$dataNodeName/$dataNodeName/".$field->name);
                if (!$res->item(0)) {
                $res = $xp->query("/data/$dataNodeName/$dataNodeName");
                    $node = $res->item(0)->appendChild($xml->createElement(  $field->name));
                } else {
                    $node = $res->item(0);
                }
                
                if ($node) {
                    if ($node->firstChild) {
                        $vs = $xml->createElement("values");
                        $node->replaceChild($vs,$node->firstChild);
                    } else {
                        $vs = $node->appendChild($xml->createElement("values"));
                    }
                    foreach ($data as $id => $value) {
                        $v = $xml->createElement("value");
                        $v->setAttribute("id",$id);
                        $v->appendChild($xml->createTextNode(html_entity_decode( $value, ENT_COMPAT, 'UTF-8')));
                        $vs->appendChild($v);
                    }
                }
                
            }
        }
        
        return $xml;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function doAdditionalQueries($type, $form) {
        foreach($form->fields as $field) {
            $field->doAdditionalQuery($form->currentID);
        }
    }
    
}

?>
