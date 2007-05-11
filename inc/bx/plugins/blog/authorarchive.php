<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
/**
 * class bx_plugins_blog_authorarchive
 * @package bx_plugins
 * @subpackage blog
 * */
class bx_plugins_blog_authorarchive {
    
    static function getContentById() {
       $db = $GLOBALS['POOL']->db;
       $tableprefix = $GLOBALS['POOL']->config->getTablePrefix();
       $res = $db->query("select user_login from ".$tableprefix."users");
       
       if (MDB2::isError($res)) {
           return "<error/>";
       }
       $xml = '<archive>';
       while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
           $xml .= '<author>'.$row['user_login'].'</author>';

           
       }
       $xml .= '</archive>';
       
       return $xml;
       
    }
}
?>
