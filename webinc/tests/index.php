<?php

error_reporting(E_ALL);


    include_once("../../conf/config.inc.php");

    if (!defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', BX_INCLUDE_DIR.'simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    require_once(SIMPLE_TEST . 'web_tester.php');

    $test = new GroupTest('All tests');
    $test->addTestFile('bx_collection.php');
    $test->addTestFile('web.php');
    $test->run(new HtmlReporter());
    
?>
