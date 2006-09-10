<?php


class Test_of_bx_helpers_string extends UnitTestCase {
    function __construct() {
        $this->UnitTestCase();
    }
    function test_makeUri() {
        $this->assertEqual(bx_helpers_string::makeUri("foo"),"foo","No Conversion. %s");
        $this->assertEqual(bx_helpers_string::makeUri("föo@"),"foeo-at","Umlaut conversion. %s");
        $this->assertEqual(bx_helpers_string::makeUri("foo6"),"foo6","Numbers without _: %s");
        $this->assertEqual(bx_helpers_string::makeUri("foo_6"),"foo-6","Numbers with -: %s");
        $this->assertEqual(bx_helpers_string::makeUri("foo_a6"),"foo_a6","Numbers with _ and letter: %s");
        $this->assertEqual(bx_helpers_string::makeUri("a!ç#'\".$%z"),"a-z","Specialchars: %s");
        $this->assertEqual(bx_helpers_string::makeUri(""),"none","Empty: %s");
        $this->assertEqual(bx_helpers_string::makeUri("a\nb\rc"),"abc","line breaks: %s");
        $this->assertEqual(bx_helpers_string::makeUri("foobar.pdf"),"foobar-pdf","Remove dots: %s");
        $this->assertEqual(bx_helpers_string::makeUri("foobar.pdf",true),"foobar.pdf","Preserve dots: %s");
    }
    
    function test_truncate() {
        $text = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum est. Integer volutpat magna ut lacus.";
        $this->assertEqual(bx_helpers_string::truncate($text),"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum est. Integer volutpat magna ...","Default values. %s");
        $this->assertEqual(bx_helpers_string::truncate($text, 48),"Lorem ipsum dolor sit amet, consectetuer ...","Length = 48. %s");
        $this->assertEqual(bx_helpers_string::truncate($text, 48, true),"Lorem ipsum dolor sit amet, consectetuer adip ...","Length = 48, Breakwords = true. %s");
        $this->assertEqual(bx_helpers_string::truncate($text, 48, true,'>>>'),"Lorem ipsum dolor sit amet, consectetuer adip >>>","Length = 48, Breakwords = true, etc = '>>>'. %s");

    }
    
    function test_utf2entities() {
         
         $this->assertEqual(bx_helpers_string::utf2entities("Zürich",true),"Z&#252;rich","Zürich. %s");
         $this->assertEqual(bx_helpers_string::utf2entities(html_entity_decode("&#1488;",ENT_QUOTES,'utf-8'),true),"&#1488;","Hebrew: %s");
         $this->assertEqual(bx_helpers_string::utf2entities(html_entity_decode("&#1580;",ENT_QUOTES,'utf-8'),true),"&#1580;","Arabic: %s");
        $this->assertEqual(bx_helpers_string::utf2entities(html_entity_decode("&#12395;",ENT_QUOTES,'utf-8'),true),"&#12395;","Hiragana. %s");
    }
    
    
    function test_makeLinksClickable() {
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/"),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/.hello"),'<a href="http://www.flux-cms.org/.hello">http://www.flux-cms.org/.hello</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/. hello"),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>. hello');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/,hello"),'<a href="http://www.flux-cms.org/,hello">http://www.flux-cms.org/,hello</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/, hello"),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>, hello');

        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/test.html#test"),'<a href="http://www.flux-cms.org/test.html#test">http://www.flux-cms.org/test.html#test</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("bar http://www.flux-cms.org/ foo"),'bar <a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a> foo');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/ foo"),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a> foo');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("bar http://www.flux-cms.org/"),'bar <a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("http://www.flux-cms.org/<"),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a><');
        $this->assertEqual(bx_helpers_string::makeLinksClickable(">http://www.flux-cms.org/<"),'>http://www.flux-cms.org/<');
        $this->assertEqual(bx_helpers_string::makeLinksClickable("(http://www.flux-cms.org/)"),'(<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>)');
        
        $this->assertEqual(bx_helpers_string::makeLinksClickable("https://www.flux-cms.org/"),'<a href="https://www.flux-cms.org/">https://www.flux-cms.org/</a>');
        $this->assertEqual(bx_helpers_string::makeLinksClickable('<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>'),'<a href="http://www.flux-cms.org/">http://www.flux-cms.org/</a>');
    }
}

