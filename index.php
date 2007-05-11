<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG <contact@liip.ch>                                    |
// +----------------------------------------------------------------------+
//
// $Id$

//define('BX_STAGE','edit');

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml');



$BX_config['popoon']['sm2php_xsl_dir'] = BX_POPOON_DIR.'/sitemap';
$BX_config['popoon']['cacheDir'] = BX_PROJECT_DIR.'tmp/';

$sitemap = new popoon (BX_PROJECT_DIR."/sitemap/sitemap.xml",$_GET['path'],
$GLOBALS['POOL']->config
);

