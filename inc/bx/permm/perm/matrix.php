<?php

/**
 * The permissions matrix class autorizes actions based on the perms table
 */
class bx_permm_perm_matrix {

    function __construct() {

    }

    /**
     * If true a permissions link is created in each collection's overview
     */
    public function isEditable()
    {
        return true;    
    }

    /**
     * Check if the requested actions may be performed by the user
     * 
     * @param uri requested uri (most be a collection)
     * @param actions array of actions
     * @param userId reference to tabel users
     * @return true if all actions may be performed
     */
    public static function isAllowed($uri, $actions, $userId) {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();

        // TODO: remove following lines
        // the blog perms are needed further down, to temporarily
        // fix blog plugin issues. once these are fixed, you can remove
        // the following lines:
        $blog_plugin = bx_plugins_blog::getInstance('admin');
        $blog_permissions = $blog_plugin->getPermissionList();

        // make sure the first char is a slash
        if($uri{0} != '/') {
            $uri = '/'.$uri;
        }

        // files' subdirs are not managed by the CMS
        if(strpos($uri, '/files/') === 0) {
            $uri = '/files/';
        }

        // predefined roles
        if($userId) {
            $roles = "'anonymous', 'authenticated'";    
        } else {
            $roles = "'anonymous'"; 
        }

        $cache = false;

        foreach($actions as $action) {

            $localUri = $uri;

            // copies simple perm behaviour
            if($action == "read" or $action == "read_navi") {
                // real simple caching for frontend read requests
                $cache_id = $uri.'-'.$action.'-'.$userId;

                // TODO enable caching
                // if(isset($_SESSION['_authsession'][$cache_id])) {  
                //     return $_SESSION['_authsession'][$cache_id];
                // } else { 
                //     $cache = $cache_id;
                //     $_SESSION['_authsession'][$cache] = false;
                // }

                // get the path of the $uri
                $localUri = substr($uri, 0, strrpos($uri, '/')+1);
                $action = "collection-front-".$action;

                // the collection based approach doesn't work well with blog's virtual folders
                if(strpos($localUri, "/blog/") === 0) {
                    $localUri = "/blog/";
                }
            } 
            else if($action == "admin" or $action == "edit") {
                // admin and edit permissions are global
                $localUri = '/permissions/';
                $action = "permissions-back-admin";
            }
            else if($action == "isuser") {
                if(!$userId) {
                    return false;
                }
                continue;
            }
            else if($action == "ishashed") {
                if (!empty($_GET['ah']) && $_GET['ah'] == bx_helpers_perm::getAccessHash()) {
                    $_SESSION['fluxcms']['ah'] = $_GET['ah'];
                    continue;
                } else if (!empty($_SESSION['fluxcms']['ah']) && $_SESSION['fluxcms']['ah'] == bx_helpers_perm::getAccessHash()) {
                    continue;
                } else {
                    return false;
                }
            }

            // TODO: fix openid permission requests wherever needed, 
            //       then remove the following lines: (HINT: shouldn't this be called something like 'openid-back-access' ?)
            if ($action == "openid") {
                continue;
            }
            // TODO: fix blog permissions in the blog plugin, then remove the following lines:
            if (strpos($action, 'blog') === 0) {
                if ($action == "blog-back-comments") {
                    // there seem to be two permissions for comments:
                    // blog-blog-comments and admin_dbforms2-back-blogcomments
                    // for now only admin_dbforms2-back-blogcomments is listed
                    // by the blog's getPermissionList() methode...
                    continue;
                }
                if (strpos($action, "blog-back-") === 0) {
                    // the blog editor's handlePOST() methode does a strange permission request.
                    // say for a post with "test" as URI, it would call isAllowed with the permission
                    // "blog-back-tes".
                    if (! in_array($action, $blog_permissions)) {
                        $action = "blog-back-post";
                    }
                }
            }

            list($plugin, $level, $name) = explode("-", $action);

            $inherit = false;
            if ($localUri != '/' && $localUri != "/dbforms2/") {
                // inheritance is not checked upon the '/'
                // root collection
                $query = "  
                    SELECT p.action
                    FROM {$prefix}perms p 
                    WHERE p.plugin='{$plugin}' 
                    AND p.action='no inheritance' 
                    AND p.uri='{$localUri}'";
                $result = $GLOBALS['POOL']->db->queryOne($query);

                if ($result === null) {
                    // always inherit, exept if there is a 
                    // record with the "no inheritance" action
                    $inherit = true;
                }
            }

            if($inherit) {
                $matches = array();
                $matched = preg_match("#(.*/)[^/]*/#", $localUri, $matches);
                if ($matched) {
                    $parent = $matches[1];
                    if(bx_permm_perm_matrix::isAllowed($parent, array($action), $userId) == false) {
                        return false;                   
                    }
                } else {
                    // something went wrong
                    return false;
                }
            }
            else {
                // no inheritance, so get the permission 
                // associated with this request
                $query = "  
                    SELECT p.id 
                    FROM {$prefix}perms p 
                    JOIN {$prefix}users2groups u2g ON u2g.fk_group=p.fk_group 
                    WHERE p.plugin='{$plugin}' 
                    AND p.action='{$action}' 
                    AND p.uri='{$localUri}' 
                    AND u2g.fk_user='{$userId}'";

                //echo "<br/>$query<br/>";

                $perms = $GLOBALS['POOL']->db->queryOne($query);


                if($perms === null) {
                    // no permission found, try again with the predefined roles
                    // TODO: merge the two of them into one query
                    $query = "
                        SELECT p.id 
                        FROM {$prefix}perms p 
                        JOIN {$prefix}groups g ON g.id=p.fk_group 
                        WHERE p.plugin='{$plugin}'  
                        AND p.action='{$action}' 
                        AND p.uri='{$localUri}' 
                        AND g.name IN ({$roles})";

                    $perms = $GLOBALS['POOL']->db->queryOne($query);

                    if($perms === null) {
                        // deny by default

                        //file_put_contents("debug.txt", $localUri . ' ' . implode(',',$actions) . ' ' . $userId);
                        //bx_helpers_debug::webdump($uri . ' ' . implode(',',$actions) . ' ' . $userId);  
                        return false;

                    }
                }        
            }

            if($cache !== false) {
                // read/navi permission granted, cache it
                $_SESSION['_authsession'][$cache] = true;
                $cache = false;
            }
        }

        return true;
    }

    public function movePermissions($from_uri, $to_uri)
    {
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $perm_table = $prefix . "perms";

        $from_uri_length = strlen($from_uri) + 1;

        $query = "
            UPDATE $perm_table
            SET    uri = CONCAT('$to_uri', SUBSTRING(uri, $from_uri_length))
            WHERE  uri LIKE '$from_uri%'
            "; 
        $GLOBALS['POOL']->db->queryOne($query);
    }
}
?>
