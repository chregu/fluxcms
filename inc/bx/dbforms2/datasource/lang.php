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

 <dbform:field name="language" descr="Language" type="select" default="">
 <dbform:datasource type="lang" ></dbform:datasource>
 </dbform:field>

 */

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_datasource_lang {

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
            self::$instance = new bx_dbforms2_datasource_lang();
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


        $table = $prefix.'options';

        $where = " name = 'outputLanguages' ";

        $sql = "
            SELECT
                *
            FROM
                $table AS t1
            WHERE
                $where
        ";

        $res = $this->db->query($sql);
        if(MDB2::isError($res)) {
            return array();
        }
        $result = array();
        $result[''] = "None";

        $row = $res->fetchRow();
        $rows = explode(';', $row[1]);

        foreach($rows as $row) {
            $result[$row] = $row;
        }

        return $result;
    }




}


