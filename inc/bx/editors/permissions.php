<?php

class bx_editors_permissions extends bx_editor implements bxIeditor {    
    
    public function getDisplayName() {
        return "Permissions";
    }

	public function getPipelineParametersById($path, $id) {
		
		return array('pipelineName'=>'permissions');
    }
    
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
 	
     	foreach($plugins as $p) {
     		if(count($p->getPermissionList()) > 0) {
     			
     			$list = '\''.implode('\',\'', $p->getPermissionList()).'\'';
	     		$query = "	DELETE  
							FROM {$prefix}perms 
							WHERE ({$prefix}perms.uri='{$url}' OR {$prefix}perms.uri='/dbforms2/')  
							AND ({$prefix}perms.action IN ({$list}) OR {$prefix}perms.inherit!='')";
				
				$GLOBALS['POOL']->dbwrite->exec($query);
     		}
     	}
		        	
		foreach(array_keys($data) as $selection) {
			
			$localUrl = $url;
			
			list($plugin, $action, $grpid) = explode('+', $selection);	
			
			if($plugin == 'admin_dbforms2') {
				$localUrl = '/dbforms2/';
			} 
			
			if($plugin != '_all') {
				
				if($action == 'inherit') {
					
					$inherit = substr($url, 0, strrpos($url, '/', -2)+1);
					
					$query = "
						INSERT INTO {$prefix}perms ( `fk_group` , `plugin` , `action` , `uri` , `inherit` ) 
						VALUES ( 
						'', '{$plugin}', '', '{$localUrl}', '{$inherit}');";
									
					$GLOBALS['POOL']->dbwrite->exec($query);
				} else {
					$query = "
						INSERT INTO {$prefix}perms ( `fk_group` , `plugin` , `action` , `uri` , `inherit` ) 
						VALUES ( 
						'{$grpid}', '{$plugin}', '{$action}', '{$localUrl}', '');";
						
					$GLOBALS['POOL']->dbwrite->exec($query);
				}
			}
		}
    }
    
    public function getEditContentById($id) {

		$perm = bx_permm::getInstance();	
		if (!$perm->isAllowed('/permissions/',array('permissions-back-manage'))) {
        	throw new BxPageNotAllowedException();
    	}

     	$parts = bx_collections::getCollectionUriAndFileParts($id);
     	return $this->generateMatrixView($id);
    }
 
    protected function generateMatrixView($id)
    {
		$i18n = $GLOBALS['POOL']->i18nadmin;
     	
     	$parts = bx_collections::getCollectionUriAndFileParts($id);
     	$url = '/'.$parts['rawname'];
     	$plugins = $this->getPlugingList($url);
     	
     	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
     	
		$query = "	SELECT g.* 
					FROM {$prefix}groups g";
    	$groups = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);

     	$xml = "
     	<permissions>
		<h3>Permissions for {$url}</h3>
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
			<th class='stdBorder'>Permission</th>";
	     	foreach($groups as $grp) {
	     	$xml .= "<th class='stdBorder'>{$grp['name']}</th>";
	     	}
	    $xml .= "</tr>";
	    
     	foreach($plugins as $p) {
     		if(count($p->getPermissionList()) > 0) {
     			
     			$list = '\''.implode('\',\'', $p->getPermissionList()).'\'';
	     		$query = "	SELECT p.* 
							FROM {$prefix}perms p 
							WHERE (p.uri='{$url}' OR p.uri='/dbforms2/') 
							AND (p.action IN ({$list}) OR p.inherit!='')";

				$dbPerms = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
     			
     			//bx_helpers_debug::webdump($dbPerms); 
     			
     			$checked = "";
     			if($this->isInherited($dbPerms, $p->name) !== false) {
     				$checked = "checked='checked'";
     			}
	     		$xml .= "<tr><td><b>{$p->name} plugin</b> (Inherit <input type='checkbox' name='{$p->name}+inherit' {$checked}/>)</td></tr>";
	     		
	     		foreach($p->getPermissionList() as $action) {
	     			$translated = $i18n->getText('act_'.$action);
	     			$xml .= "<tr><td>{$translated}</td>";

					$localPlugin = $p->name;

     				list($actPlugin, $actLevel, $actName) = explode('-', $action);
					if($actPlugin == 'admin_dbforms2') {
						$localPlugin = 'admin_dbforms2';
					}
	     			
	     			foreach($groups as $grp) {
   				
	     				$checked = "";
		     			if($this->isChecked($dbPerms, $localPlugin, $grp['id'], $action) == true) {
		     				$checked = "checked='checked'";
		     			}
     			
	     				$xml .= "<td align='center'><input type='checkbox' name='{$localPlugin}+{$action}+{$grp["id"]}' {$checked}/></td>";
	     			}
	     			
	     			$xml .= "</tr>";
	     		}
     		}
     	}
     	$xml .= "
	    </table><br/>
		<input type='submit' name='bx[plugins][admin_edit][_all]' value='Update' class='formbutton'/>
		</form>
		</permissions>";
     	
     	return domdocument::loadXML($xml);	
    }
    
    protected function getPlugingList($url) {
    	$collection = bx_collections::getCollection($url);
     	$plugins = $collection->getChildrenPlugins();
     	
     	// add collection plugin by default
     	$permColl = new bx_plugins_collection();
     	$permColl->name = "collection";
     	$plugins[] = $permColl;
     	
     	return $plugins;
    }
  
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
    
    protected function isInherited($dbPerms, $plugin)
    {
    	foreach($dbPerms as $perm) {
    		if($perm['inherit'] != "" and $perm['plugin'] == $plugin) {
    			return $perm['inherit'];
    		}	
    	}
    	return false;
    }
}

?>
