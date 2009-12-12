<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', '../../simpletest/');
}

if (! defined('BX_PROJECT_DIR')) {
    define('BX_PROJECT_DIR', realpath('../../../'));
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once('bxwebtestcase.php');

require_once(  'config-test.php');



class Test_of_installer extends BxWebTestCase {
    
    function setUp() {
        
        @rename(BX_PROJECT_DIR."/conf/config.xml",BX_PROJECT_DIR."/conf/config.xml.old");
    }
    
    function tearDown() {
        if (!file_exists(BX_PROJECT_DIR."/conf/config.xml")) {
            copy(BX_PROJECT_DIR."/conf/config.xml.old",BX_PROJECT_DIR."/conf/config.xml");
        }
    }
    
    function test_startpage() {
        //first page
        $this->get($GLOBALS['testsuite']['host']);
        $this->assertWantedPattern('/Welcome to the Flux CMS Installation Routine/i');
        
        //second page
        $this->get($GLOBALS['testsuite']['host']);
        $this->assertWantedPattern('/CMS Properties/');
        // set fields
        $this->assertField("cms.user","admin");
        $this->setField("cms.user",$this->getOption('cms.user'));
        $this->setField("cms.password",$this->getOption('cms.password'));
        
        $this->setField("dir.sub",$this->getOption('dir.sub'));
        
        
        $this->setField("database.name",$this->getOption('database.name'));
        $this->setField("database.user",$this->getOption('database.user'));
        $this->setField("database.password",$this->getOption('database.password'));
        $this->setField("database.host",$this->getOption('database.host'));
        $this->setField("database.prefix",$this->getOption('database.prefix'));
        
        $this->setField("databaseRoot.User",$this->getOption('databaseRoot.User'));
        $this->setField("databaseRoot.Password",$this->getOption('databaseRoot.Password'));

        
        $this->clickSubmit('Start Installation');
        
        
        $this->get($GLOBALS['testsuite']['host']);
        $this->assertWantedPattern('/Flux CMS Demo/i',"Installation didn't succeed. %s");
        
        //check for errors
        
        $this->assertNoCmsErrors();
        
        
    }
    
}

$test = &new Test_of_installer();
$test->run(new TextReporter());