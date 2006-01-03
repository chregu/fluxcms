<?php


class Test_of_blog extends BxWebTestCase {
    
    function test_startpage() {
        
        $this->get($this->host."blog/");
        $this->assertWantedText('Your first Post',"First post not found error. \n %s");
        $this->assertNoCmsErrors();
        //click permalink
        $this->clickLink("Permalink");
        $this->assertNoCmsErrors();
        $this->assertText("add a comment","Add a comment not found. \n %s");
        $fix = substr(md5(time()),0,5);
        $this->setField("bx_fw[name]","name $fix");
        $this->setField("bx_fw[email]","$fix@example.org");
        $this->setField("bx_fw[base]","http://$fix.example.org");
        $this->setField("bx_fw[comments]","hi. I'm $fix.");
        $this->clickSubmit("Send");
        $this->assertWantedPattern("#<a href=\"http:\/\/$fix.example.org\">name $fix</a>#");
        $this->assertWantedText("I'm $fix.","Comment not found. \n %s");
        $this->assertNoCmsErrors();
        
        $this->loginAdmin();
        $this->setFrameFocus("edit");

        //delete comment
        $this->clickLink("Blog Posts Overview / Latest Comments");
        $this->assertWantedText("I'm $fix.","Comment not found. \n %s");
        // get id
        $browser = $this->getBrowser();
        $urls = $browser->_page->getUrlsByLabel("hi. I'm $fix.");
        $this->assertTrue(count($urls) > 0,"Comment URL not found. \n %s");
        $id = substr($urls[0]->getEncodedRequest(),4);
        $this->assertTrue(is_numeric($id),"ID is not a number. \n %s");
        // set field to be deleted
        $this->setField("bx[plugins][admin_edit][deletecomments][$id]","$id");
        $this->clickSubmit("Delete Selected Comments");
        $this->assertNoUnwantedText("I'm $fix.","Comment still there.\n %s");
        
        
    }
    
}