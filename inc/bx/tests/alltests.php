<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', '../../simpletest/');
}
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once('bxwebtestcase.php');

include_once("../../../inc/bx/init.php");

bx_init::start('conf/config.xml', "../../..");
require_once(  'config-test.php');

function usage($msg='') {

    if($msg) echo "Error:\t".$msg."\n\n";
    echo "Usage:\alltests.php [testid|all]\n";
    echo "Options:\n";
    echo "\ttestid\t\tID of test to be run. Run without arguments to see available tests.\n";
    echo "\tall\t\tRuns all tests found in scandirs.\n";
    die(1);
}

if($argc>2) {
    usage();
}
include_once("testhelper/scanner.php");
$testhelper = new testhelper_scanner();
$acaselist = $testhelper->getTestCaseList();

if($argc!=2) {

    print("\nAvailable TestCases:\n");
    for($i=0;$i<count($acaselist);$i++) {

        print("[".$i."] ".$acaselist[$i]."\n");

    }
    print("[all] Run all TestCases found.\n");

    $pin = fopen('php://stdin', 'r');
    $szruncase = trim(fgets(STDIN));
}

if($argv[1]!='')
    $szruncase = $argv[1];

if($szruncase!='all' && $acaselist[$szruncase]=='')
    usage("TestCase not found!\n");

$aruncaselist = ($szruncase=='all') ? $acaselist : array($acaselist[$szruncase]);

include_once("testhelper/runner.php");


if(!testhelper_runner::main(new TextReporter(), $aruncaselist)) {
    
    die(1);
}



die(0);


?>