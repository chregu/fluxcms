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
// $Id: fluxcli.php 6796 2006-04-25 13:15:55Z philipp $

ini_set('html_errors', 0);
define('ID', '$Id: fluxcli.php 6796 2006-04-25 13:15:55Z philipp $');

$commands = array(
    'sendmails',
);

$options = array(
    'verbose' => FALSE,
);


if(!file_exists('inc/bx/init.php')) {
    echo "ERROR: please change to the project root and call me again.\n";
    exit(1);
}

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml', '');
$db = $GLOBALS['POOL']->db;


function printHelp() {
    echo "Flux CMS Newsmailer Command Line Interface, ".ID."\n";
    echo "Usage: newsmailer.php [options] <command> [parameters]

send all mails in queue:
    newsmailer.php sendmails
";
    exit(1);
}

function printVerbose($msg) {
    if(VERBOSE)
        echo $msg;
}

function checkArgumentCount($arguments, $count) {
    if(sizeof($arguments) < $count) {
        echo "ERROR: too few arguments\n";
        printHelp();
    }
    return TRUE;
}

function _command_sendmails($options, $arguments) {

	$newsmailer = bx_editors_newsmailer_newsmailer::newsMailerFactory("newsmailer");
	$mailserver = $newsmailer->getDefaultMailServer();
    $options = $newsmailer->getMailserverOptions($mailserver);
    		
	$newsmailer->finalizeSend($options, 200);

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
    if(call_user_func('_command_'.$arguments[0], $options, array_slice($arguments, 1))) {
        exit(0);
    }
} else {
    echo "ERROR: unknown command: '".$arguments[0]."'\n";
    printHelp();
}

exit(1);

?>
