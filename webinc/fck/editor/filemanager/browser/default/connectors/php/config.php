<?php 
/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: config.php
 * 	Configuration file for the File Manager Connector for PHP.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

global $Config ;

// SECURITY: You must explicitelly enable this "connector". (Set it to "true").
$Config['Enabled'] = true ;

// Path to user files relative to the document root.

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Usefull if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
include_once("../../../../../../../../inc/bx/init.php");
bx_init::start('conf/config.xml', "../../../../../../../..");

$conf = bx_config::getInstance();
$confvars = $conf->getConfProperty('permm');
$permObj = bx_permm::getInstance($confvars);
if (!$permObj->isAllowed('/',array('admin'))) {
     
     print "Access denied";
     
     die();
 }
 
$Config['UserFilesPath'] = "/";
$Config['UserFilesAbsolutePath'] = BX_OPEN_BASEDIR . $Config['UserFilesPath']   ;

if (isset($_GET['Type']) && $_GET['Type'] == 'gallery') {
$Config['UserFilesPath'] .= "/files/_galleries/";
$Config['UserFilesAbsolutePath'] .= "/files/_galleries/";
}


$Config['AllowedExtensions']['files']	= array() ;
$Config['DeniedExtensions']['files']		= array('php','php3','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;

/*$Config['AllowedExtensions']['gallery']	= array() ;
$Config['DeniedExtensions']['gallery']		= array('php','php3','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;
*/
/*
$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;

$Config['AllowedExtensions']['Media']	= array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['Media']	= array() ;
*/
?>
