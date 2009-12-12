<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * Helper functions to be used when working with db2xml. Reduces the task
 * of getting XML from a database to one function call.
 *
 * @package
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_helpers_db2xml {

    /**
     *  Returns a DOMDocument object which contains the data queried from the
     *  database by the passed query.
     *
     *  @param  string $query SQL query to be run.
     *  @param  boolean $fromMaster Whether to use the master DB or not in a master/slave setup.
     *  @access public
     *  @return object DOMObject containing the data returned from the query.
     */
    public static function getXMLByQuery($query, $fromMaster = false, $root = 'data') {
        if ($fromMaster) {
            $xml = new XML_db2xml($GLOBALS['POOL']->dbwrite, $root, 'Extended');
        } else {
            $xml = new XML_db2xml($GLOBALS['POOL']->db, $root, 'Extended');
        }
        $options = array(
            'formatOptions' => array ( 'xml_seperator' => '')
        );

        $xml->Format->SetOptions($options);
        $xml->add($query);
        $dom = $xml->getXMLObject();
        
        return $dom;
        
    }
}
?>
