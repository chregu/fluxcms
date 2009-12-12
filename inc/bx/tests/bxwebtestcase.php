<?php
require_once(SIMPLE_TEST .'/web_tester.php');

class BxWebTestCase extends WebTestCase {
    
    public $host = "localhost";
    
    function __construct() {
        
        parent::__construct('BX Web Test Case Class');
        
        $this->host = $GLOBALS['testsuite']['host'];

    }
    
    
    function assertNoCmsErrors() {
    
        $this->assertNoUnwantedText('BXCMSNG Errors',"BXCMSNG Errors detected. \n%s");
    }
    
    function before($method) {
            parent::before($method);
            $this->addHeader("Accept-Language: en-us;q=0.8,en;q=0.5,fr-ch;q=0.3");
    }
    
    function getOption($option) {
        
        return $GLOBALS['testsuite'][$option];
    }
    
    function loginAdmin() {
        $this->get($this->host.'admin/');
        $this->assertWantedText('Admin Login',$this->host ." error. \n %s");
        $this->assertNoCmsErrors();
        
        $this->setField("username",$this->getOption('cms.user'));
        $this->setField("password",$this->getOption('cms.password'));
        $this->clickSubmit('Submit');
        if (!$this->assertTrue($this->setFrameFocus("header"),"Header frame not found, Login did not succeed.\n %s")) {
            return false;
        }
        
    }
}