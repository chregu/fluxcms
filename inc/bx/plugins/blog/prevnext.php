<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
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
* a little subplugin for getting next and previous entry
*
* does not take into account, if a blog is publish/has-a-category yet
* it's maybe not very performant, since it has to do 2 full tablescans...
*
* Add the following to blog.xsl, if you want to use it:

    <p>
        <xsl:for-each select="document(concat('portlet://',$collectionUri,'plugin=prevnext(',substring-after(@id,'entry'),').xml'))/bx/plugin/prevnext">
            <a href="{$blogroot}archive/{prev/uri}"><xsl:value-of select="prev/title"/></a>
            || 
            <a href="{$blogroot}archive/{next/uri}"><xsl:value-of select="next/title"/></a>
        </xsl:for-each>
    </p>
*/


class bx_plugins_blog_prevnext {
    
    static function getContentById($path,$id,$params, $p = null, $tablePrefix = "") {
       
        $id = $params[0];
        
        $query = 'SELECT unix_timestamp(post_date) from '.$tablePrefix.'blogposts as blogposts where id = '.$id;
        
        $date = $GLOBALS['POOL']->db->queryOne($query);
        
        $perm = bx_permm::getInstance();
        if ($perm->isLoggedIn()) {
            $overviewPerm = 3;
        } else {
            $overviewPerm = 1;
        }
        
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        unix_timestamp(post_date) as unixtime,    
       '.$date.' - unix_timestamp(post_date)  as diff,
        blogposts.post_title
        from '.$tablePrefix.'blogposts as blogposts where id != '.$id.' and blogposts.post_status & '.$overviewPerm.'  and unix_timestamp(post_date) <= '.$date.' order by diff  limit 1';
        
        $res = $GLOBALS['POOL']->db->query("$query");
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
       $xml =  '<prevnext><prev><uri>'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html</uri>';
       $xml .= '<title>'.$row['post_title'].'</title></prev>';
       
       
        $query = 'SELECT blogposts.post_uri, blogposts.id,
        unix_timestamp(post_date) - '.$date.'     as diff,
        unix_timestamp(post_date) as unixtime, 
        blogposts.post_title
        from '.$tablePrefix.'blogposts as blogposts where id != '.$id.' and blogposts.post_status & '.$overviewPerm.'  and unix_timestamp(post_date) >= '.$date.' order by diff limit 1';
        bx_helpers_debug::dump_errorlog($query);
        $res = $GLOBALS['POOL']->db->query("$query");
        $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
       
       $xml .= '<next><uri>'.date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html</uri>';
       $xml .= '<title>'.$row['post_title'].'</title>';
       
       
       return $xml . '</next></prevnext>';
        
       
        
    }
}
?>
