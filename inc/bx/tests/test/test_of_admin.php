<?php


class Test_of_admin extends BxWebTestCase {
    
    function test_startpage() {
        
        $this->loginAdmin();
        //check if header frame is loaded and bookmark pattern is there
        $this->assertWantedPattern('/<form name="bookmarks">/',"Bookmarks form in headerframe not found. \n%s" );
        $this->assertNoCmsErrors();
        
        $this->assertTrue($this->setFrameFocus("navi"),"Navi frame not found.\n %s");
        $this->assertNoCmsErrors();    
       
        $this->assertTrue($this->setFrameFocus("edit"),"Edit frame not found.\n %s");
        $this->assertNoCmsErrors();    
        
        $this->assertWantedText('General Options',"'General Options' box in edit frame not found. \n %s");
       
        
    }
    
}