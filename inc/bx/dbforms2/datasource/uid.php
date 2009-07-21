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
// $Id: field.php 4831 2005-06-30 14:41:20Z philipp $


/*
 example:

 <dbform:field name="uri" descr="URI" type="select" default="">
 <dbform:datasource type="uid" subtree="/produkte/"></dbform:datasource>
 </dbform:field>
  
 */

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_datasource_uid {

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
            self::$instance = new bx_dbforms2_datasource_uid();
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
        $prefix = (isset($args['tablePrefix']) && !empty($args['tablePrefix'])) ? $args['tablePrefix'] : $GLOBALS['POOL']->config->getTablePrefix();


        $table = $prefix.'properties';

        $where = " 1 AND ";

        if (isset($args['where'])) {
            $where = $args['where']. " AND ";
        }

        if (isset($args['subtree'])) {
            $where .= " t1.path LIKE '{$args['subtree']}%' AND ";
        }

        if (isset($args['order'])) {
            $order = " ORDER BY {$args['order']} ";
        } else {
            $order = " ORDER BY t1.path";
        }

        
        $sql = "SELECT t2.value AS _id, t1.path as _title
        FROM $table AS t1, $table AS t2 WHERE 
        $where
        t1.name='output-mimetype'
        AND t1.value='httpd/unix-directory' 
        AND t2.name='unique-id'
        AND t1.path = t2.path
        $order ";
        
        $res = $this->db->query($sql);
        if(MDB2::isError($res)) {
            return array();
        }
        $result = array();
        $result[''] = "None";
        while ($row = $res->fetchRow()) {
            $result[$row[0]] = $row[1];
        }
        return $result;
    }




}


