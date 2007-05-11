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
/*

if you want to use rating plugin put this table into your database =)

    CREATE TABLE `".$tablePrefix."blograting` (
    `id` int(11) NOT NULL auto_increment,
    `postid` int(11) NOT NULL default '0',
    `blogid` int(11) NOT NULL default '0',
    `rating` int(11) NOT NULL default '0',
    `username` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`id`))",'blograting'


here is the config.xml for the blog collection

    <plugins>
        <extension type="html"/>
        <parameter type="pipeline" name="xslt" value="blog.xsl"/>
        <plugin type="blog">
            <parameter name="blogid" value="$blogid"/>
            <parameter name="blograting" value="true"/>
        </plugin>
        <plugin type="rating"></plugin>

        <plugin type="navitree"></plugin>
    </plugins>


*/

class bx_plugins_blog_rating {
    
    static function getRatingById($id) {
        
        $xml = "";
        $rating = 0;
        $divisor = 0;
        $db = $GLOBALS['POOL']->db;
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select rating from ".$tablePrefix."blograting where postid = '".$id."'";
        
        $res = $db->query($query);
        if(!MDB2::isError($res)) {
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $rating = $rating + $row['rating'];
                $divisor++;
            }
            if($rating) {
                $result = $rating / $divisor;
                
                $username = bx_helpers_perm::getUsername();
                
                $query = "select rating from ".$tablePrefix."blograting where username = '".$username."' and postid = '".$id."'";
                $res = $db->query($query);
                $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
                $xml = ' blog:rating="'.round($result).'" blog:myrating="'.$row['rating'].'" ';
                return $xml;
            } else {
                return $xml;
            }
        }
    }
}
?>