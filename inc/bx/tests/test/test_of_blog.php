<?php


class Test_of_blog extends BxWebTestCase {
    
    function test_makeNewPost() {
        $this->loginAdmin();
        $this->setFrameFocus("edit");
        $this->clickLink("Make new Blog Entry");
        $fix = substr(md5(time()),0,6);
        $this->setField("bx[plugins][admin_edit][title]","title $fix");
        $this->setField("bx[plugins][admin_edit][uri]","title $fix");
        $this->setField("bx[plugins][admin_edit][content]","content");
        
        $this->setField("bx[plugins][admin_edit][categories][General]","on");
        
        $this->clickSubmit("Save");
        
        $this->get($this->host."blog/");
        $this->assertWantedText("title $fix","New post not found error. \n %s");
        $this->get($this->host."admin/");
        $this->setFrameFocus("edit");
        $this->clickLink("Blog Posts Overview / Latest Comments");
        
        $this->assertWantedText("title $fix","Post not found. \n %s");
        
        $this->get($this->host."/admin/edit//blog/title-$fix.html");
        $id = $this->_browser->getField("bx[plugins][admin_edit][id]");
        
        $this->post($this->host."/admin/edit//blog/title-$fix.html", array("bx[plugins][admin_edit][uri]" => "/admin/edit//blog/title-$fix.html", "bx[plugins][admin_edit][delete]" => 1, "bx[plugins][admin_edit][id]" => $id));
        
        $this->get($this->host."blog/");
        $this->assertNoUnwantedText("title $fix","Post not deleted error. \n %s");
        
    }
    
    function test_startpage() {
        
        $this->get($this->host."blog/");
        $this->assertWantedText('Your first Post',"First post not found error. \n %s");
        $this->assertNoCmsErrors();
        //click permalink
        $this->clickLink("Permalink");
        $this->assertNoCmsErrors();
        $this->assertText("add a comment","Add a comment not found. \n %s");
        $fix = substr(md5(time()),0,5);
        $this->setField("name","name $fix");
        $this->setField("email","$fix@example.org");
        $this->setField("openid_url","http://$fix.example.org");
        $this->setField("comments","hi. I'm $fix.");
        
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