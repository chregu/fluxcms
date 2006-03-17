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
// $Id: field.php 4831 2005-06-30 14:41:20Z philipp $

/*
example:

 <dbform:datasource type="sql">select id, name from {tablePrefix}blogcategories</dbform:datasource>
*/

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_datasource_sql {
    
    /**
     *  DOCUMENT_ME
     *  @var var
     */
    static private $instance = null;
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    private function __construct() {
        $this->db = $GLOBALS['POOL']->db;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bx_dbforms2_datasource_sql();
        } 
        return self::$instance;
    }
    
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getValues($args, $childNode) {
        $sql = str_replace("{tablePrefix}",$GLOBALS['POOL']->config->getTablePrefix(),$childNode);
        $res = $this->db->query($sql);
        if(MDB2::isError($res)) {
            return array();
        }
        $result = array();
        while ($row = $res->fetchRow()) {
               $result[$row[0]] = $row[1];
        }
        
        return $result;
        
    }
    
}

?>