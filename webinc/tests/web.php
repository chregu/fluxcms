<?php

 
   
   class TestOfWebpage extends WebTestCase {
    function __construct() {
        $this->WebTestCase();
    }
    function testHomepage() {
        $this->assertTrue($this->get(BX_WEBROOT));
        $this->assertWantedPattern("/This is just a prototype/");
        $this->assertWantedPattern("/addresses/");
        $this->assertMime(array('text/html; charset=utf-8'));
        $this->assertWantedPattern('/addresses/'); 
        $this->assertTrue($this->get(BX_WEBROOT.'addresses/'));
        $this->assertWantedPattern('/zurich/');
        if (!$this->clickLink('zurich')) {
            $this->fail("Link 'zurich' not found");
        }
        
        $this->assertTrue($this->get(BX_WEBROOT."lb.jpg"),"Lade Lukas");
        $this->assertMime(array('image/jpeg'));
        
    }
}
   
?>