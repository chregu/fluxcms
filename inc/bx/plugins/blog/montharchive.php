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

class bx_plugins_blog_montharchive {
    
    static function getContentById($path,$id,$params, $p = null, $tablePrefix = "") {
       $db = $GLOBALS['POOL']->db;
       $perm = bx_permm::getInstance();

        $colluri =  bx_collections::getCollectionUri($path);
        $blogid =  $p->getParameter($colluri,"blogid");
        if (!$blogid) {$blogid = 1;};
        
       if ($perm->isLoggedIn()) {
           $overviewPerm = 3;
       } else {
           $overviewPerm = 1;
       }
       $q="select  count(*) as count, date_format(post_date,'%M') as monthlong, date_format(post_date,'%m') as month, year(post_date) as year from ".$tablePrefix."blogposts as blogposts  where  blogposts.id > 0 and blog_id = ".$blogid." and blogposts.post_status & $overviewPerm group by year(post_date), month(post_date) order by post_date DESC";
       $res = $db->query($q);
       if ($db->isError($res)) {
           return "<error/>";
       }
       $xml = '<archive  xmlns:i18n="http://apache.org/cocoon/i18n/2.1">';
       while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
           $xml .= '<link count="'.$row['count'].'" href="'.$row['year'].'/'.$row['month'].'/">';
           $xml .= '<i18n:text>'.$row['monthlong'].'</i18n:text> '. $row['year'];
           $xml .= '</link>';
           
       }
       $xml .= '</archive>';
       
       return $xml;
       
    }
}
?>
