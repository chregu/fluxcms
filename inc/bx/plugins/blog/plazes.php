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

class bx_plugins_blog_plazes {
    
     static public function onInsertNewPost($post) {
        
        $info = self::getPlazes($post->author);
        $post->appendInfoString($info);
        return $post;
    }
 

    static public function getPlazes($author) {
        $query=("select plazes_username, plazes_password from "
        .$GLOBALS['POOL']->config->getTablePrefix()."users where user_login = ". $GLOBALS['POOL']->db->quote($author));
         
        require_once('XML/RPC.php');
        
        $row = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);
        if (MDB2::isError($row)) {
            throw new PopoonDBException($row);
        }
        
        if (!$row['plazes_username']) {
            return "";
        }
        $params = array(new XML_RPC_Value($row['plazes_username'], 'string'), new XML_RPC_Value($row['plazes_password'], 'string'));
        $ask = new XML_RPC_Message('plazes.whereami', $params);
        $rpc = new XML_RPC_Client('/xmlrpc/whereami.php', 'beta.plazes.com');
        
        $resp = $rpc->send($ask);
        
        if (!$resp) {
            echo 'Communication error: ' . $resp->errstr;
            return "";
        }
        if ($resp->faultCode()) {
            error_log( "Plazes error: " .$resp->faultString());
            return "";
        }
        $value = $resp->value();
     
        $plaze = $value->getval();
        if (!is_array($plaze)) {
            return "";
        }
        if ($plaze['plazelon'] > 181) {
            error_log("Plazes error, Longitude was bigger than 180");
            return "";
        }
        $xml = "<plazes>\n";
        if (isset($plaze['username'])) {
            unset($plaze['username']);
        }
        foreach($plaze as $key => $value){
        
            $xml .= " <$key>".$value."</$key>\n";
        }
        $xml .= "</plazes>";
       return $xml;
    }
}
?>
