<?php
Class bx_versioning_diff implements bx_versioning_interface {
	
	private static $old = null;

	private static $edits = Array();
	
	public static function getInstance($opts) {
		if (!function_exists('xdiff_string_diff')) {
			bx_log::log("xdiff extension is not properly installed, versioning with diff will not work");
			return FALSE;
		}
		return new bx_versioning_diff();
	}
	
	public function commit($rpath, $path, $log='') {		
		$diff = xdiff_string_diff(self::$old, file_get_contents($rpath), 0, TRUE);
		
		if (!$diff) {
			return TRUE;
		}
		
		$db = $GLOBALS['POOL']->dbwrite;
		$sQuery = 'INSERT INTO `' . $GLOBALS['POOL']->config->getTablePrefix() . 'history_diff` SET diff_path="' . $db->escape($path, true) . '", diff_value="' . $db->escape($diff,true) . '"';
		$res = $db->query($sQuery);
		
		
	}
	
    public function getListById ($id) {
    	if (isset($_POST['load']) && isset($_POST['id']) && $_POST['load'] == 1 && $did = intval($_POST['id']) ) {
    		$this->loadById($did);
    	}
    	
    	
    	$db = $GLOBALS['POOL']->db;
    	
    	$uri = $db->escape($id,TRUE);
    	
    	
    	// TODO: Subqueries for collections
    	$sQuery = 'SELECT diff_path, diff_id, diff_timestamp, diff_value FROM ' . $GLOBALS['POOL']->config->getTablePrefix() . 'history_diff WHERE diff_path = "' . $uri . '"ORDER BY diff_timestamp DESC';
    	
    	
    	$res = $db->query($sQuery);
    	
    	if ($res->numRows() <= 0) {
    		return FALSE;
    	}
    	
    	$aResult = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
    	
    	$current = file_get_contents(BX_DATA_DIR.$aResult[0]['diff_path']);
    	$old = $current;
    	
    	
    	for ($i = 0; $i < count($aResult); $i++) {
    		$old = xdiff_string_patch($old, $aResult[$i]['diff_value'], XDIFF_PATCH_REVERSE, $err);
    		
    		
    		$d = new Text_Diff('auto', Array(explode("\n",$current), explode("\n",$old)));
    		$r = new Text_Diff_Renderer_unified();
    		foreach ($d->getDiff() as $line) {
    			if ($line instanceof Text_Diff_Op_add) {
    				$aResult[$i]['diff_tohead']['ins'][] = $line->final;
    			}
    			elseif ($line instanceof Text_Diff_Op_delete) {
    				$aResult[$i]['diff_tohead']['del'][] = $line->orig;
    			}
    			elseif ($line instanceof Text_Diff_Op_change) {
    				$aResult[$i]['diff_tohead']['change'][] = $line->orig;
    				$aResult[$i]['diff_tohead']['change'][] = $line->final;
    			}
    			elseif ($line instanceof Text_Diff_Op_copy) {
    				$aResult[$i]['diff_tohead']['copy'][] = $line->orig;
    				$aResult[$i]['diff_tohead']['copy'][] = $line->final;
    			}
    		}
    		$aResult[$i]['diff_timestamp'] = date("d.m.Y H:i",strtotime($aResult[$i]['diff_timestamp']));
    		
    	}
    	
    	
    	
    	$dom = new DOMDocument();
    	$dom->appendChild($dom->createElement('history'));
    	$dom->documentElement->appendChild($entries = $dom->createElement('entries'));
    	$dom->documentElement->setAttribute('path',$uri);
    	
    	bx_helpers_xml::array2Dom($aResult, $dom, $entries);    	
    	
    	
    	
    	return $dom;
    }
    
    public function loadById ($id) {

    	$db = $GLOBALS['POOL']->db;
    	
    	$sQuery = 'SELECT diff_path, diff_id, diff_timestamp, diff_value FROM ' . $GLOBALS['POOL']->config->getTablePrefix() . 'history_diff WHERE diff_path = (SELECT diff_path FROM ' . $GLOBALS['POOL']->config->getTablePrefix() . 'history_diff WHERE diff_id = ' . $id . ') AND diff_timestamp >= (SELECT diff_timestamp FROM ' . $GLOBALS['POOL']->config->getTablePrefix() . 'history_diff WHERE diff_id = ' . $id . ')'
    				. ' ORDER BY diff_timestamp DESC';
    	$res = $db->query($sQuery);
    	
    	if (MDB2::isError($res) || $res->numRows() <= 0) {
    		return FALSE;
    	}
    	
    	$aResult = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
    	
    	
    	//file_get_contents($aResult[0]['diff_path'])
    	$filepath = BX_DATA_DIR.$aResult[0]['diff_path'];
    	
    	$diffTs = $aResult[0]['diff_timestamp'];
    	$diffPath = $aResult[0]['diff_path'];
    	
    	$head = file_get_contents($filepath);
    	$old = $head;
    	
    	foreach($aResult as $row) {
    		$head = xdiff_string_patch($head,$row['diff_value'],XDIFF_PATCH_REVERSE,$err);
    	}
    	
    	bx_log::log($err);
		
    	
    	$this->setOld($old);
    	
    	file_put_contents($filepath, $head);
    	
    	$this->commit($filepath, $aResult[0]['diff_path']);
    	
    }
	
	public function setOld ($pOld) {
		self::$old = $pOld;
	}
	
	public function init() {}
}