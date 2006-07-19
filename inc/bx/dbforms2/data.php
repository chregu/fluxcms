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
     *  Parses all values of the given DOM-document and returns an array.
     *
     *  @param  object $form The bx_dbforms2_form object which the data belongs to.
     *  @param  DOMDocument $xml The DOM-document to be parsed.
     *  @access public
     *  @return array Array containing all values.
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
     *  Translates a XML-node to a PHP data type.
     *
     *  @param  DOMElement $dataNode The node to be translated
     *  @access protected
     *  @return mixded The PHP representation of the XML-node
     */
    protected static function translateValue($dataNode) {
        if ($dataNode->childNodes->length == 1) {
            $childNode = $dataNode->firstChild;
            if ($childNode->nodeType == 1) {
                if ($childNode->nodeName == "values") {
                    $values = array();
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
     *  Adds additional data to an existing DOMDocument.
     *
     *  @param  object $form The bx_dbforms2_form object which the data belongs to.
     *  @param  DOMDocument $xml The DOM-document containing the existing data.
     *  @access public
     *  @return DOMDocument The DOM-document with additional data added.
     */
    public static function addAdditionalDataByForm($form, $xml) {
        
        $xp = new domxpath($xml);
        $dataNodeName = $form->tablePrefix.$form->tableName;
        
        if (is_array($form->fields)) {
            $_f = $form->fields;
            foreach($_f as $field) {
                if($field instanceof bx_dbforms2_field)  {
                
                    $data = $field->getAdditionalData($form->currentID);
                    if($data) {
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
                
                } else {
                    /*
                    
                    Silvan: What's this for? It doubles all entries on the save return :)
                    ----
                    $value = $field->getValue();
                
                    if ($value && !empty($value)) {
                    
                        $res = $xp->query("/data/$dataNodeName/$dataNodeName/".$field->name);
                        if ($res->length == 0) {
                            $parent =  $xp->query("/data/$dataNodeName/$dataNodeName");   
                            if ($parent->item(0) && $parent->item(0) instanceof DOMElement) {
                                $p = $parent->item(0);   
                                $node = $xml->createElement($field->name);
                                $p->appendChild($node);
                            }
                        } else {
                            $node = $res->item(0);    
                        }
                    
                        if ($node instanceof DOMElement) { 
                            $node->appendChild($xml->createTextNode(html_entity_decode($value, ENT_COMPAT, 'UTF-8')));
                        }
                    }*/
                }
            }

            
            
        }

        return $xml;
    }
    
    /**
     *  Executes additional Queries on a form which are not part of the main form query.
     *
     *  @param  object $form The form on which the additional queries should be executed.
     *  @access public
     */
    public static function doAdditionalQueries($form) {
        foreach($form->fields as $field) {
            if($field instanceof bx_dbforms2_field)  {
                $field->doAdditionalQuery($form->currentID);
            }
        }
    }
    
}

?>
