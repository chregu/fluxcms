<?php


class Test_of_webpage extends BxWebTestCase {
    
    function test_startpage() {
        
        $this->get($this->host);
        $this->assertWantedPattern('/flux cms demo/i',$this->host ." error. \n %s");
        $this->assertNoCmsErrors();
        
        
    }
    
}