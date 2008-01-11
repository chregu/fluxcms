<?php

/**
 * This class allows to edit the matrix permissions
 */
class bx_editors_permissions extends bx_editor implements bxIeditor {    

    public function getDisplayName() {
        return "Permissions";
    }

    public function getPipelineParametersById($path, $id) {

        return array('pipelineName'=>'permissions');
    }

    /**
     * Save permissions
     */
    public function handlePOST($path, $id, $data) {

        $parts = bx_collections::getCollectionUriAndFileParts($id);

        $perm = bx_permm::getInstance();    
        if (!$perm->isAllowed('/permissions/',array('permissions-back-manage'))) {
            throw new BxPageNotAllowedException();
        }

        $prefix = $GLOBALS['POOL']->config->getTablePrefix();

        $parts = bx_collections::getCollectionUriAndFileParts($id);
        $url = trim('/'.$parts['rawname'], '.');

        $plugins = $this->getPlugingList($url);

        // remove the permissions set first 
        // because the post request doesn't tell us if the user ungranted a permission
        foreach($plugins as $p) {
            if(count($p->getPermissionList()) > 0) {

                $list = '\''.implode('\',\'', $p->getPermissionList()).'\'';
                $query = "
                    DELETE  
                    FROM {$prefix}perms 
                    WHERE (uri='{$url}' OR uri='/dbforms2/')  
                    AND (action IN ({$list}) OR action ='no inheritance')
                    ";

                $GLOBALS['POOL']->dbwrite->exec($query);
            }
        }

        // the following is a hack to fix the dbforms2 plugin. 
        // that way, the 'no inheritance' field will be rewritten
        // for now, the permission matrix plugin is
        // concentrationg on the XHTML and collection plugin
        if (!isSet($data['admin_dbforms2'])) {
            $data['admin_dbforms2'] = array("dummy");
        }

        // iterate through the selected permissions
        foreach($data as $plugin => $actions) {
            // just in case
            if(!is_array($actions)) continue;

            // dbforms2 permissions are global and not url bound
            if($plugin == 'admin_dbforms2') {
                $localUrl = '/dbforms2/';
            } else {
                $localUrl = $url;
            }

            if (!isSet($actions['inherit'])) {
                // inheritance is tracked negatively,
                // i.e. we inherit if not stated differently
                $query = "
                    INSERT INTO {$prefix}perms ( `fk_group` , `plugin` , `action` , `uri` ) 
                    VALUES ( 
                        '', '{$plugin}', 'no inheritance', '{$localUrl}'
                    );
                ";
                $GLOBALS['POOL']->dbwrite->exec($query);
            } else {
                // we do not save any data
                // when we inherit...
                continue;
            }

            foreach ($actions as $action => $groups) {

                // just in case
                if (!is_array($groups)) continue;

                // dbforms2 permissions are global and not url bound
                if (strpos($action, "admin_dbforms2") === 0) {
                    $localUrl = '/dbforms2/';
                } else {
                    $localUrl = $url;
                }

                foreach ($groups as $group => $void) {

                    $query = "
                        INSERT INTO {$prefix}perms ( `fk_group` , `plugin` , `action` , `uri` ) 
                        VALUES ( 
                            '{$group}', '{$plugin}', '{$action}', '{$localUrl}');";


                    $GLOBALS['POOL']->dbwrite->exec($query);

                }
            }
        }
    }

    /**
     * User requested the matrix permission system gui
     */
    public function getEditContentById($id) {

        $perm = bx_permm::getInstance();    
        if (!$perm->isAllowed('/permissions/',array('permissions-back-manage'))) {
            throw new BxPageNotAllowedException();
        }

        $parts = bx_collections::getCollectionUriAndFileParts($id);
        return $this->generateMatrixView($id);
    }

    /**
     * Creates the permissions editor view
     */
    protected function generateMatrixView($id)
    {
        $i18n = $GLOBALS['POOL']->i18nadmin;

        $txtInherit = $i18n->getText("Inherit");
        $txtPerm = $i18n->getText("Permission");
        $txtPermFor = $i18n->getText("Permissions for");
        $txtUpdate = $i18n->getText("Update");

        // get plugins
        $parts = bx_collections::getCollectionUriAndFileParts($id);
        $url = '/'.$parts['rawname'];
        $plugins = $this->getPlugingList($url);

        $prefix = $GLOBALS['POOL']->config->getTablePrefix();

        $query = "  SELECT g.* 
            FROM {$prefix}groups g";
        $groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

        // create a matrix with the collection's plugin permissions and the available groups
        $xml = "
            <permissions>
            <h3>{$txtPermFor} {$url}</h3>
            <form name='bx_perms' action='#' method='post'>
            <table>
            <cols>
            <col width='250'></col>";
        foreach($groups as $grp) {
            $xml .= "<col width='120'></col>";
        }
        $xml .= "
            </cols>
            <tr>
            <th class='stdBorder'>{$txtPerm}</th>";
        foreach($groups as $grp) {
            $xml .= "<th class='stdBorder'>{$grp['name']}</th>";
        }
        $xml .= "</tr>";

        foreach($plugins as $p) {
            if(count($p->getPermissionList()) > 0) {

                $list = '\''.implode('\',\'', $p->getPermissionList()).'\'';
                $query = "  SELECT p.* 
                    FROM {$prefix}perms p 
                    WHERE (p.uri='{$url}' OR p.uri='/dbforms2/') 
                    AND (p.action IN ({$list}) OR p.action ='no inheritance')";

                $dbPerms = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

                $checked = "";
                $xml .= "<tr><td><b>{$p->name} plugin</b> ";

                $isInherited = false;
                if($url != "/") {
                    $isInherited = $this->isInherited($dbPerms, $p->name);
                    if($isInherited !== false) {
                        $checked = "checked='checked'";
                    }
                    // root can't inherit
                    $xml .= "({$txtInherit} <input type='checkbox' name='{$p->name}[inherit][0]' {$checked}/>)";
                }
                // POST info about every plugin, even those where everything is uncecked...
                $xml .= "<input type='checkbox' style='display:none' name='{$p->name}[plugin]' checked='checked'/>";
                $xml .= "</td></tr>";

                foreach($p->getPermissionList() as $action) {
                    $translated = $i18n->getText('act_'.$action);
                    $xml .= "<tr><td>{$translated}</td>";

                    $localPlugin = $p->name;

                    // dbforms2 is a special case because the forms are global and not url bound
                    list($actPlugin, $actLevel, $actName) = explode('-', $action);
                    if($actPlugin == 'admin_dbforms2') {
                        $localPlugin = 'admin_dbforms2';
                    }

                    foreach($groups as $grp) {

                        $checked = "";
                        if(!$isInherited && $this->isChecked($dbPerms, $localPlugin, $grp['id'], $action) == true) {
                            $checked = "checked='checked'";
                        }

                        $xml .= "<td align='center'><input type='checkbox' name='{$localPlugin}[{$action}][{$grp["id"]}]' {$checked}/></td>";
                    }

                    $xml .= "</tr>";
                }
            }
        }
        $xml .= "
            </table><br/>
            <input type='submit' name='bx[plugins][admin_edit][_all]' value='{$txtUpdate}' class='formbutton'/>
            </form>
            </permissions>";

        return domdocument::loadXML($xml);  
    }

    /**
     * Get the list of plugins associated with this url
     * 
     * @param url requested url
     * @return array of plugin instances
     */
    protected function getPlugingList($url) {
        $collection = bx_collections::getCollection($url);
        $plugins = $collection->getChildrenPlugins();

        if ($url = "/") {
            $plugins = $this->recurse($collection, $plugins);
        }


        // add collection plugin by default
        $permColl = new bx_plugins_collection();
        $permColl->name = "collection";
        $plugins[] = $permColl;

        //echo "url:" . $url . "<br/>";
        //var_dump($plugins);
        //echo "<br/>";

        // die(var_dump($plugins));
        return $plugins;
    }

    protected function recurse($collection, $plugins) {
        foreach($collection->getChildren() as $child) {
            if($child instanceof bx_collection) {
                $plugins = array_merge($child->getChildrenPlugins(), $this->recurse($child, $plugins));
            }
        }
        return $plugins;
    }

    /**
     * Check if the permission is currently granted
     */
    protected function isChecked($dbPerms, $plugin, $group, $action)
    {
        foreach($dbPerms as $perm) {
            if(empty($perm['inherit']) and $perm['plugin'] == $plugin and
                $perm['fk_group'] == $group and $perm['action'] == $action) { 
                    return true;
                }   
        }
        return false;
    }

    /**
     * Check if the plugin permissions are currenty inherited from its parent
     */
    protected function isInherited($dbPerms, $plugin)
    {
        foreach($dbPerms as $perm) {
            if($perm['action'] == "no inheritance" and $perm['plugin'] == $plugin) {
                return false;
            }   
        }
        return true;
    }
}

?>
