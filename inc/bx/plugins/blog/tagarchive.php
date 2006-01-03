<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+

class bx_plugins_blog_tagarchive {
    
    static function getContentById() {
       $db = $GLOBALS['POOL']->db;
       $tableprefix = $GLOBALS['POOL']->config->getTablePrefix();
       $res = $db->query("select DISTINCT tag from ".$tableprefix."tags left join ".$tableprefix."properties2tags on ".$tableprefix."tags.id = ".$tableprefix."properties2tags.tag_id where ".$tableprefix."tags.id = ".$tableprefix."properties2tags.tag_id");
       
       if ($db->isError($res)) {
           return "<error/>";
       }
       $xml = '<archive>';
       while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
           $xml .= '<tag>'.$row['tag'].'</tag>';

           
       }
       $xml .= '</archive>';
       
       return $xml;
       
    }
}
?>
