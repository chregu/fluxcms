<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('./conf/config.inc.php');

if (!isset($_GET['path'])) {
    $_GET['path'] = "index.html";
 //   $_GET['path'] = "/admin/editpopup/projekte/index.html";
}

$BX_config['popoon']['sm2php_xsl_dir'] = BX_POPOON_DIR.'/sitemap';
$BX_config['popoon']['cacheDir'] = BX_PROJECT_DIR.'tmp/';


//include_once(BX_POPOON_DIR."/popoon.php");
$sitemap = new popoon (BX_PROJECT_DIR."/sitemap/sitemap.xml",$_GET['path'],
$bx_config
);

//print $GLOBALS['POOL']->db->debugOutput();

bx_helpers_debug::log_memory_usage();
//bx_helpers_debug::dump_incFiles(true);

?>

