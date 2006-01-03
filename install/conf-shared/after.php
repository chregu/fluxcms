<?php
$host = $_SERVER['HTTP_HOST'];
if (preg_match("#^w+\.#",$host)) {
	$host = preg_replace("#^w+\.#","",$host);
	header("Location: http://$host".$_SERVER['REQUEST_URI']);
	die();
} else if (strpos($host,"freeflux.org") > 0) {
 $host = preg_replace("#\.org$#",".net",$host);
        header("Location: http://$host".$_SERVER['REQUEST_URI']);
        die();
} else if (strpos($host,"freeflux.ch") > 0) {
 $host = preg_replace("#\.ch$#",".net",$host);
        header("Location: http://$host".$_SERVER['REQUEST_URI']);
        die();
}


	

$db = $GLOBALS['POOL']->db;

$res = $db->query('Select db, prefix, ads from freeflux_master.master where active = 1 and host = '.$db->quote($host));
// FIXME dbprefix is allowed to be differently to hosts prefix...
if ($db->isError($res)) {
    throw new PopoonDBException($res);
} 
if (!$db->isError($res)) { 
    $row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
    $prefix = $row['prefix'];
    
    
    if ($prefix) {
        define('BX_OPEN_BASEDIR',BX_PROJECT_DIR.'hosts/'.$prefix.'/');
        define('BX_DATA_DIR',BX_OPEN_BASEDIR.'/data/'); 
        define('BX_THEMES_DIR',BX_OPEN_BASEDIR.'/themes/');
        //define('BX_TMP_DIR',BX_OPEN_BASEDIR.'/tmp/'); 
        $GLOBALS['POOL']->config->dsn['tableprefix'] = $prefix.'_';
        mysql_select_db($row['db']);
	$GLOBALS['POOL']->config->ads = $row['ads'];
        $GLOBALS['POOL']->config->dsn['database'] = $row['db'];
        $GLOBALS['POOL']->config->permm['authModule']['dsn'] =   $GLOBALS['POOL']->config->dsn ;
        $GLOBALS['POOL']->config->permm_http['authModule']['dsn'] =   $GLOBALS['POOL']->config->dsn ;

        
    } else {
        die($_SERVER['HTTP_HOST'] . " not defined");
    }
}
else {
        die("DB Error");
}

$GLOBALS['POOL']->config->setOutputCacheCallback("bx_cachecallback");


function bx_cachecallback() {
   if (strpos($_GET['path'],"admin/") !== false) {
        return false;
        }
    return 304;
}

?>
