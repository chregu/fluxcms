<?php
// +----------------------------------------------------------------------+
// | Bitflux CMS                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@liip.ch>                        |
// +----------------------------------------------------------------------+

class bx_editors_dbform_main {
    
    function __construct() {
        
        
        //include_once("XML/sql2xml_ext.php");
        //include_once("bitlib/xsl/processor.php");
        
        //PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'handle_pear_error');
        
        //the admin class is the main class. almost everything should theoretically go in there, so we just have to call that
        $admin = new bx_editors_dbform_manager();
        
        $admin->setTablePrefix($GLOBALS['POOL']->config->getTablePrefix());
        
        
        $admin->setConfigFile(realpath("config.xml"));
        
        $admin->idField = "id";
        
        $admin->getTableInfo() ;
        $admin->getFieldsInfo() ;
        
        /*$dsn = $admin->getDsn();
        if ($dsn instanceof MDB2_Driver_Common) {
            $db = $dsn; 
        } else {
            $db = MDB2::connect($dsn);
        }*/
        $admin->setDB($db);
        $oldID  =isset($admin->http_vars[$admin->idField]) ? $admin->http_vars[$admin->idField] : 0;
        //makes an update or an insert to the db, if needed
        $admin->insertUpdateFields();
        
        //checks if an id was delivered, otherwise it takes the first one from the db.
        $admin->checkId($oldID);
        
        //initializes the sql2xml class and the config file
        $admin->setXml();
        
        //$admin->setSessionVars();
        $admin->setAuthVars();
        $admin->setQueryString();
        //gets the values for the master entry
        $admin->setMasterXml();
        
        //gets the values for foreign keys and this stuff
        $admin->setAdditionalSql();
        //chooser
        $admin->setChooser();
        // does the xsl transformation and all that stuff...
        if (isset ( $_REQUEST["getXML"]) && $_REQUEST["getXML"] ==1 ) {
            header("Content-Type: text/xml");
            
            $GLOBALS['_html_trans']['&lt;'] = '<';
            $GLOBALS['_html_trans']['&gt;'] = '>';
            $GLOBALS['_html_trans']['&quot;'] = '"';
            $GLOBALS['_html_trans']['&apos;'] = "'";
            $GLOBALS['_html_trans']['&nbsp;'] = '&nbsp;';
            $GLOBALS['_html_trans']['&amp;'] = '&amp;';
            print   utf8_encode(str_replace("&nbsp;","&amp;nbsp;",str_replace(array_keys($GLOBALS["_html_trans"]),array_values($GLOBALS["_html_trans"]),preg_replace("/\&amp;([#a-z0-9A-Z]+);/","&$1;",    $admin->getIt(BX_BITLIB_DIR."php/bitlib/admin/xsl/getxml.xsl")))));
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            $admin->printIt();
        }
    }
}

?>
