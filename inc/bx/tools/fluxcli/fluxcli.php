<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

ini_set('html_errors', 0);
define('ID', '$Id$');

$commands = array(
    'collectioncreate',
    'collectiondelete',
    'propertyset',
);

$options = array(
    'verbose' => FALSE,
);


echo "Flux CMS Command Line Interface, ".ID."\n";

if(!file_exists('inc/bx/init.php')) {
    echo "please change to the project root and call me again.\n";
    die();
}

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml', '');
$db = $GLOBALS['POOL']->db;


function printHelp() {
    echo "Usage: fluxcli.php [options] <command> [parameters]

create a new collection:
    fluxcli.php collectioncreate <collection uri>

delete a collection:
    fluxcli.php collectiondelete <collection uri>

set a property:
    fluxcli.php propertyset <path> <name> <value> [namespace]
    
";
    die();
}

function printVerbose($msg) {
    if(VERBOSE)
        echo $msg;
}

function _command_collectioncreate($options, $arguments) {
    if(sizeof($arguments) < 1) {
        echo "ERROR: too few arguments\n";
        printHelp();
    }
    
    printVerbose("creating collection '".$arguments[0]."'...\n");
    $maincoll = bx_collections::getCollection($arguments[0]);
    $coll = new bx_collection($arguments[0].'/', 'output', TRUE);
    
    if($coll instanceof bx_collection) {
        echo "collection '".$arguments[0]."' successfully created.\n";
        return TRUE;
    } else {
        echo "ERROR: unable to create collection '".$arguments[0]."'\n";
    }
    
    return FALSE;
    
}

function _command_collectiondelete($options, $arguments) {
    if(sizeof($arguments) < 1) {
        echo "ERROR: too few arguments\n";
        printHelp();
    }
    
    printVerbose("deleting collection '".$arguments[0]."'...\n");

    $parts = bx_collections::getCollectionAndFileParts($arguments[0], 'admin');
    if ($parts['coll']->deleteResourceById($parts['rawname'])) {
        echo "collection '".$arguments[0]."' successfully deleted.\n";
        return TRUE;
    } else {
        echo "ERROR: unable to delete collection '".$arguments[0]."'\n";
    }
    
    return FALSE;
    
}

function _command_propertyset($options, $arguments) {
    if(sizeof($arguments) < 3) {
        echo "ERROR: too few arguments\n";
        printHelp();
    }
    
    $ns = BX_PROPERTY_DEFAULT_NAMESPACE;
    if(isset($arguments[3]) && !empty($arguments[3])) {
        $ns = $arguments[3];
    }
    
    printVerbose("setting property '".$arguments[1]."' on '".$arguments[0]."'...\n");

    $parts  = bx_collections::getCollectionAndFileParts($arguments[0], 'admin');
    $coll = $parts['coll'];
    $id = $parts['rawname'];
    
    if ($id == '')  {
       $res = $coll;
    } else {
       $plugin = $coll->getPluginById($id);
       if ($plugin instanceof bxIplugin) {
           $res = $plugin->getResourceById($coll->uri, $id);
       } else {
           echo "No matching plugin found. Can't set property. ('". $arguments[0]."' might be an invalid path)\n";
           return FALSE;
       }
    }
    
    $res->setProperty($arguments[1], bx_helpers_string::utf2entities(utf8_encode($arguments[2])), $ns);
    echo "property successfully set.\n";
    return TRUE;
}


$argv = $_SERVER['argv'];
array_shift($argv);

$arguments = array();
$parameters = array();

foreach($argv as $arg) {
    if($arg{0} == '-' && empty($arguments)) {
        switch($arg) {
            case '-v':
            case '--verbose':
                $options['verbose'] = TRUE;
            break;
            case '-h':
            case '--help':
                printHelp();
            default:
                echo "ERROR: unknown option: '$arg'\n";
                printHelp();
        }
    } else {
        $arguments[] = $arg;
    }
}

if(empty($arguments)) {
    printHelp();
}

define('VERBOSE', $options['verbose']);

if(in_array($arguments[0], $commands)) {
    call_user_func('_command_'.$arguments[0], $options, array_slice($arguments, 1));
} else {
    echo "ERROR: unknown command: '".$arguments[0]."'\n";
    printHelp();
}



?>
